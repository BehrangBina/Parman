"""
Convert a Google-Docs-made Persian PDF (e.g. اساسنامه) into WordPress block HTML for the
document reader (theme inc/documents.php).

Needs `pdftotext` (poppler). Usage:
    python tools/pdf-to-reader.py input.pdf output.html [--skip-pages 1]

What it fixes (all seen in the party PDFs):
  * bidi control characters pdftotext wraps around every line
  * displaced zero-width non-joiner:  "م ‌یرود" -> "می‌رود",  "بنیا ‌نگذاران" -> "بنیان‌گذاران"
  * lines wrapped mid-sentence are joined back into paragraphs
Structure:
  * "ماده N ـ ..."            -> <h3>
  * "الف) ..." / "ب) ..." etc. -> <h4>   (short lines only)
  * lines starting with ● / •  -> <ul><li>
Always proofread the result: the fixes are rules, not a reading of the text.
"""
import html
import re
import subprocess
import sys

BIDI = dict.fromkeys(map(ord, '‪‫‬‭‮‎‏⁦⁧⁨⁩'), None)
ZWNJ = '‌'
SENTENCE_END = ('.', ':', '؛', '؟', '!', '»', ')')
LETTER_ITEM = re.compile(r'^(الف|ب|پ|ت|ث|ج|چ|ح|خ|د|ذ|ر|ز|ژ|س|ش)\)\s*')
ARTICLE = re.compile(r'^ماده\s*[۰-۹0-9]+\s*ـ')
BULLET = re.compile(r'^[●•▪◦]\s*')


def extract(pdf, first):
    out = subprocess.run(['pdftotext', '-enc', 'UTF-8', '-f', str(first), pdf, '-'],
                         capture_output=True, check=True)
    return out.stdout.decode('utf-8')


LETTERS = 'الف|ب|پ|ت|ث|ج|چ|ح|خ|د|ذ|ر|ز|ژ|س|ش'


def clean_line(line):
    line = line.translate(BIDI).replace('\f', '')
    # "X ‌Y" / "X ‌ Y" (space(s) around a ZWNJ) -> "XY‌": the letter after the ZWNJ belongs before it
    line = re.sub(r'(\S) ' + ZWNJ + r' ?(\S)', r'\1\2' + ZWNJ, line)
    line = re.sub(r'[ \t]{2,}', ' ', line)
    return line.strip()


def fix_punctuation(text):
    # Persian comma / period / semicolon landed on the next word: "X ،Y" -> "X، Y"
    text = re.sub(r'\s+([،؛.])(?=\S)', r'\1 ', text)
    text = re.sub(r'\s+([،؛])', r'\1', text)
    # "ماده ۱ـ" -> "ماده ۱ ـ"
    text = re.sub(r'([۰-۹0-9])ـ', r'\1 ـ', text)
    # "تبصره -۱متن" / "تبصره ۱-متن" -> "تبصره ۱ - متن"
    text = re.sub(r'تبصره\s*-\s*([۰-۹0-9]+)\s*', r'تبصره \1 - ', text)
    text = re.sub(r'تبصره\s*([۰-۹0-9]+)\s*-\s*', r'تبصره \1 - ', text)
    return re.sub(r' {2,}', ' ', text).strip()


def split_items(paragraph):
    """Split "... . ب) ..." run-ons so every lettered item starts its own paragraph."""
    parts = re.split(r'(?<=[.:؛])\s+(?=(?:' + LETTERS + r')\)\s)', paragraph)
    return [p for p in parts if p.strip()]


def blocks(text):
    """Yield (kind, text) with kind in h3, h4, li, p."""
    para = []

    def flush():
        if para:
            yield ('p', ' '.join(para))
            para.clear()

    # pdftotext joins several visual lines into one, as right-to-left runs "‫…‬ ‫…‬".
    # A run starting with an item marker ("ب) مرامنامه") starts a new line; a short one is
    # the item's title and gets a line of its own. Other runs are soft wraps of one paragraph.
    marker = re.compile(r'^‫?(?:' + LETTERS + r')\)')
    lines = []
    for raw in text.splitlines():
        current = []
        for run in raw.split('‬ ‫'):
            if marker.match(run):
                if current:
                    lines.append(' '.join(current))
                    current = []
                if len(clean_line(run)) < 60:
                    lines.append(run)
                    continue
            current.append(run)
        if current or not raw.strip():
            lines.append(' '.join(current))

    for raw in lines:
        line = clean_line(raw)
        if not line:
            yield from flush()
            continue
        if ARTICLE.match(line):
            yield from flush()
            yield ('h3', line)
        elif LETTER_ITEM.match(line) and len(line) < 70 and not line.endswith('.'):
            yield from flush()
            yield ('h4', line)
        elif BULLET.match(line):
            yield from flush()
            yield ('li', BULLET.sub('', line))
        else:
            para.append(line)
            if line.endswith(SENTENCE_END):
                yield from flush()
    yield from flush()


def emphasise(t):
    """Bold the item marker ("الف)") or note label ("تبصره ۱ -") at the start of a paragraph."""
    t = re.sub(r'^((?:' + LETTERS + r')\))', r'<strong>\1</strong>', t)
    return re.sub(r'^(تبصره [۰-۹0-9]+ -)', r'<strong>\1</strong>', t)


def to_gutenberg(items):
    out, in_list = [], False
    expanded = []
    for kind, text in items:
        text = fix_punctuation(text)
        if kind == 'p':
            expanded += [('p', part) for part in split_items(text)]
        else:
            expanded.append((kind, text))
    for kind, text in expanded:
        t = html.escape(text, quote=False)
        if kind == 'p':
            t = emphasise(t)
        if kind != 'li' and in_list:
            out.append('</ul>\n<!-- /wp:list -->')
            in_list = False
        if kind == 'li':
            if not in_list:
                out.append('<!-- wp:list -->\n<ul>')
                in_list = True
            out.append(f'<li>{t}</li>')
        elif kind == 'h3':
            out.append(f'<!-- wp:heading {{"level":3}} -->\n<h3>{t}</h3>\n<!-- /wp:heading -->')
        elif kind == 'h4':
            out.append(f'<!-- wp:heading {{"level":4}} -->\n<h4>{t}</h4>\n<!-- /wp:heading -->')
        else:
            out.append(f'<!-- wp:paragraph -->\n<p>{t}</p>\n<!-- /wp:paragraph -->')
    if in_list:
        out.append('</ul>\n<!-- /wp:list -->')
    return '\n\n'.join(out) + '\n'


if __name__ == '__main__':
    args = sys.argv[1:]
    skip = 0
    if '--skip-pages' in args:
        i = args.index('--skip-pages')
        skip = int(args[i + 1])
        del args[i:i + 2]
    pdf, dest = args
    items = list(blocks(extract(pdf, skip + 1)))
    with open(dest, 'w', encoding='utf-8') as fh:
        fh.write(to_gutenberg(items))
    counts = {k: sum(1 for x in items if x[0] == k) for k in ('h3', 'h4', 'li', 'p')}
    print(f'{dest}: {counts}')
