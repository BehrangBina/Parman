"""
Cuts the photo panel out of the Figma card exports (Titles/Titles/Cards/**.png) so it can be used
as the person's photo in the admin ("اعضای حزب"). The panel is found as the non-white area on
the left half of the card; its height follows the card photo's 134 x 162 proportion.

    python tools/crop-person-photos.py [name-filter]

Writes Titles/photos/<file>.png (gradient background included, as in the design).
"""
import glob
import os
import sys

from PIL import Image

ROOT = os.path.join(os.path.dirname(__file__), '..', 'Titles')
RATIO = 162 / 134  # Figma card photo box


def crop(path):
    image = Image.open(path).convert('RGBA')
    width, height = image.size
    pixels = image.load()
    xs, ys = [], []
    for y in range(height):
        for x in range(int(width * 0.45)):  # the photo panel; the name starts further right
            r, g, b, a = pixels[x, y]
            if a > 200 and not (r > 248 and g > 248 and b > 248):
                xs.append(x)
                ys.append(y)
    left, right, bottom = min(xs), max(xs) + 1, max(ys) + 1
    top = max(0, round(bottom - (right - left) * RATIO))
    return image.crop((left, top, right, bottom))


if __name__ == '__main__':
    name_filter = sys.argv[1].lower() if len(sys.argv) > 1 else ''
    out_dir = os.path.join(ROOT, 'photos')
    os.makedirs(out_dir, exist_ok=True)
    for path in sorted(glob.glob(os.path.join(ROOT, 'Titles', 'Cards', '**', '*.png'), recursive=True)):
        if name_filter and name_filter not in os.path.basename(path).lower():
            continue
        photo = crop(path)
        target = os.path.join(out_dir, os.path.basename(path).replace(' ', '-'))
        photo.convert('RGB').save(target, optimize=True)
        print(os.path.relpath(target, ROOT), photo.size)
