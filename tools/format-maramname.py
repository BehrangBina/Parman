"""
Turns the motto (مرامنامه) PDF text into reader HTML with its real structure, and restores the
half-spaces (ZWNJ) that this PDF lost. Run after tools/pdf-to-reader.py:

    python tools/pdf-to-reader.py docs/content/source/maramname.pdf docs/content/maramname.raw.html --skip-pages 1   # intermediate, not kept
    python tools/format-maramname.py docs/content/maramname.raw.html docs/content/maramname.html

Structure: title, سرآغاز, the twelve founding lines (یکم … دوازدهم), then فصل (chapter, h3),
اصل (principle, h4), clauses "۱-۱" as paragraphs and "•" items as lists.
Half-spaces: plural ها/های, the verb prefix می, and a checked list of compounds — only where the
missing half-space is visible (the first part ends in a joining letter). Proofread the result.
"""
import html
import io
import re
import sys

ZWNJ = '‌'
FA = '؀-ۿ'

# Typos made by the PDF text layer.
TYPOS = [
    (' ,', '،'), (',', '،'),
    ('آ ِن', 'آنِ'), ('ارجمند ِی', 'ارجمندیِ'), ('پایدا ِر', 'پایدارِ'),
    ('برخوردار باشد(. بر اساس قانون مدنی کشور ایران)', 'برخوردار باشد (بر اساس قانون مدنی کشور ایران).'),
    ('بنیادگرایانه ی', 'بنیادگرایانه‌ی'), ('پایه ی', 'پایه‌ی'), ('پیشینه ی', 'پیشینه‌ی'),
    ('دولت-ملت های', 'دولت-ملت‌های'), ('می گردد', 'می‌گردد'), ('خشکسالی های', 'خشکسالی‌های'),
]

# Compounds checked by hand (first part ends in a joining letter, so the missing ZWNJ shows).
COMPOUNDS = {
    'بهعنوان': 'به‌عنوان', 'بهمنظور': 'به‌منظور', 'بهویژه': 'به‌ویژه', 'بهگونهای': 'به‌گونه‌ای',
    'بهرهبرداری': 'بهره‌برداری', 'اندیشهپرور': 'اندیشه‌پرور', 'بازتابدهنده': 'بازتاب‌دهنده',
    'تصمیمگیریهای': 'تصمیم‌گیری‌های', 'جابهجایی': 'جابه‌جایی', 'جهتگیریهای': 'جهت‌گیری‌های',
    'حافظهبنیان': 'حافظه‌بنیان', 'خدشهناپذیر': 'خدشه‌ناپذیر', 'دههی': 'دهه‌ی', 'وظیفهی': 'وظیفه‌ی',
    'رابطهورزی': 'رابطه‌ورزی', 'سرمایهگذاری': 'سرمایه‌گذاری', 'سهرنگ': 'سه‌رنگ',
    'شایستهسالاری': 'شایسته‌سالاری', 'شناختهشده': 'شناخته‌شده', 'مهارتبنیان': 'مهارت‌بنیان',
    'میهنسالاری': 'میهن‌سالاری', 'میهنپرستی': 'میهن‌پرستی', 'همهجانبه': 'همه‌جانبه',
    'گرانبها': 'گران‌بها', 'گفتوگوناپذیر': 'گفت‌وگوناپذیر', 'جداییناپذیر': 'جدایی‌ناپذیر',
    'بیابانزایی': 'بیابان‌زایی', 'افراطگرایی': 'افراط‌گرایی', 'بنیانگذاری': 'بنیان‌گذاری',
    'سیاستگذاری': 'سیاست‌گذاری', 'قانونمداری': 'قانون‌مداری', 'ایرانگرایی': 'ایران‌گرایی',
    'مرامگرایی': 'مرام‌گرایی', 'مرامگرا': 'مرام‌گرا', 'زیستبوم': 'زیست‌بوم', 'زیستبومی': 'زیست‌بومی',
    'زیستبومهای': 'زیست‌بوم‌های', 'سیاستورزی': 'سیاست‌ورزی', 'دانشبنیان': 'دانش‌بنیان',
    'ایرانزمین': 'ایران‌زمین', 'آسیبپذیر': 'آسیب‌پذیر', 'آسیبدیده': 'آسیب‌دیده',
    'تداومبخش': 'تداوم‌بخش', 'تحمیلشده': 'تحمیل‌شده', 'چشمانداز': 'چشم‌انداز',
    'ارجگذاری': 'ارج‌گذاری', 'نخستوزیر': 'نخست‌وزیر', 'دولتسازی': 'دولت‌سازی',
    'پیشبرنده': 'پیش‌برنده', 'بدینسان': 'بدین‌سان', 'توانیاب': 'توان‌یاب', 'برونمرزی': 'برون‌مرزی',
    'جامعهای': 'جامعه‌ای', 'شیوهای': 'شیوه‌ای', 'حرفهای': 'حرفه‌ای', 'پیشهای': 'پیشه‌ای',
    'ایرانگرا': 'ایران‌گرا', 'بیریشه': 'بی‌ریشه', 'خورشیدنشان': 'خورشیدنشان',
}
NOT_PLURAL = {'تنها', 'گرانبها', 'های'}
NOT_VERB_PREFIX = ('میهن', 'میان', 'میراث')
NOT_VERB = {'میزان', 'میلیون', 'میدان', 'میدانی', 'میانه'}
NON_JOINING = 'اآأإدذرزژوۀ'  # a half-space after these letters is invisible, so none is added


def fix_word(word):
    core = word.strip('،؛:.()«»')
    if not core or ZWNJ in core:
        return word
    fixed = core
    if core in COMPOUNDS:
        fixed = COMPOUNDS[core]
    else:
        bare = core[1:] if core.startswith('نمی') else core
        if re.match(r'^ن?می[' + FA + ']{2,}$', core) and bare not in NOT_VERB and not bare.startswith(NOT_VERB_PREFIX):
            fixed = re.sub(r'^(ن?می)', r'\1' + ZWNJ, core)
        m = re.match(r'^([' + FA + ZWNJ + ']{2,}?)(ها|های|هایی)$', fixed)
        if m and core not in NOT_PLURAL and m.group(1)[-1] not in NON_JOINING:
            fixed = m.group(1) + ZWNJ + m.group(2)
    return word.replace(core, fixed)


def fix_text(text):
    text = re.sub(r'([۰-۹])(?=[^\s۰-۹\-])', r'\1 ', text)  # "۱۲۸۵خورشیدی" -> "۱۲۸۵ خورشیدی"
    text = re.sub(r'\s*[,،]\s*(?=\S)', '، ', text)         # "الف،ب" / "الف ,ب" -> "الف، ب"
    for a, b in TYPOS:
        text = text.replace(a, b)
    # Words only: digits (U+06F0-06F9) and punctuation stay outside, so glued numbers do not hide a word.
    return re.sub(r'[ء-ۯ‌]+', lambda m: fix_word(m.group(0)), text)


ORDINALS = 'دوازدهم|یازدهم|یکم|دوم|سوم|چهارم|پنجم|ششم|هفتم|هشتم|نهم|دهم'
CHAPTER = re.compile(r'فصل (یک|دو|سه|چهار|پنج|شش|هفت) ')
PRINCIPLE = re.compile(r'اصل\s*:\s*([۰-۹]+)\s*')
CLAUSE = re.compile(r'(?<![۰-۹\-])([۰-۹]+-[۰-۹]+)\s*')


def build(text):
    text = re.sub(r'\s+', ' ', text).strip()
    out = []
    head, sep, rest = text.partition('اندیشه پارمان')
    out.append('<!-- wp:heading {"level":2,"textAlign":"center"} --><h2 class="wp-block-heading has-text-align-center">مرامنامه پارمان (حزب) پادشاهی ایرانیان</h2><!-- /wp:heading -->')
    out.append('<!-- wp:heading {"level":3,"textAlign":"center"} --><h3 class="wp-block-heading has-text-align-center">سرآغاز</h3><!-- /wp:heading -->')
    out.append('<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><strong>به نام ایران</strong></p><!-- /wp:paragraph -->')
    rest = sep + rest
    intro, _, rest = rest.partition(':')
    out.append(para(intro.strip() + ':'))
    first_chapter = CHAPTER.search(rest)
    founding, body = rest[: first_chapter.start()], rest[first_chapter.start():]
    for name, line in re.findall(r'(' + ORDINALS + r')\s*:\s*(.*?)(?=(?:' + ORDINALS + r')\s*:|$)', founding.strip()):
        out.append(para('<strong>' + name + ':</strong> ' + html.escape(line.strip()), raw=True))

    tail = ''
    closing = body.find('این مرامنامه در هفت فصل')
    if closing != -1:
        body, tail = body[:closing], body[closing:]

    # Split the body into chapter / principle / clause / bullet pieces, in order.
    tokens = re.split(r'(فصل (?:یک|دو|سه|چهار|پنج|شش|هفت) |اصل\s*:\s*[۰-۹]+\s*|(?<![۰-۹\-])[۰-۹]+-[۰-۹]+\s*|•\s*)', body)
    pending = None
    items = []

    def flush_items():
        if items:
            out.append('<!-- wp:list --><ul class="wp-block-list">' + ''.join('<li>' + html.escape(i) + '</li>' for i in items) + '</ul><!-- /wp:list -->')
            items.clear()

    for token in tokens:
        if not token or not token.strip():
            continue
        if CHAPTER.fullmatch(token):
            flush_items()
            pending = ('chapter', token.strip())
        elif PRINCIPLE.fullmatch(token):
            flush_items()
            pending = ('principle', PRINCIPLE.fullmatch(token).group(1))
        elif CLAUSE.fullmatch(token):
            flush_items()
            pending = ('clause', token.strip())
        elif token.strip() == '•':
            pending = ('bullet', '')
        else:
            text_part = token.strip()
            kind, value = pending if pending else ('text', '')
            if kind == 'chapter':
                out.append('<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' + html.escape(value + ': ' + text_part) + '</h3><!-- /wp:heading -->')
            elif kind == 'principle':
                out.append('<!-- wp:heading {"level":4} --><h4 class="wp-block-heading">' + html.escape('اصل ' + value + ': ' + text_part) + '</h4><!-- /wp:heading -->')
            elif kind == 'clause':
                out.append(para('<strong>' + value + '</strong> ' + html.escape(text_part), raw=True))
            elif kind == 'bullet':
                items.append(text_part)
            else:
                out.append(para(text_part))
            pending = None
    flush_items()
    if tail:
        out.append(para(tail.strip()))
    return '\n\n'.join(out) + '\n'


def para(content, raw=False):
    body = content if raw else html.escape(content)
    return '<!-- wp:paragraph --><p>' + body + '</p><!-- /wp:paragraph -->'


if __name__ == '__main__':
    source, target = sys.argv[1], sys.argv[2]
    raw = io.open(source, encoding='utf-8').read()
    raw = re.sub(r'<li>', ' • ', raw)  # list items from the PDF converter are separate bullets
    raw = re.sub(r'<[^>]+>', ' ', raw)
    text = fix_text(html.unescape(raw))
    io.open(target, 'w', encoding='utf-8', newline='\n').write(build(text))
    print('written', target)
