#!/usr/bin/env python3
"""Checks that every English page in docs/ has a German counterpart in docs/de/ that matches it.

- same permalink and nav_order, lang en / de
- same headings (level and order), the German ones carry the anchor of the English heading ({#id}), so links like
  translations#collection-setup work in both languages
- identical code blocks, code is maintained once and only the text is translated

Run from the repository root: python3 docs/_tools/check-translations.py
"""

import glob
import os
import re
import sys

DOCS = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..')


def kramdown_id(text):
    # the auto id of kramdown: letters, digits, spaces and dashes, lower case
    text = re.sub(r'^[^a-zA-Z]+', '', text)
    text = re.sub(r'[^a-zA-Z0-9 -]', '', text)
    return text.replace(' ', '-').lower()


def parse(path):
    with open(path, encoding='utf-8') as file:
        content = file.read()
    front_matter = dict(re.findall(r'^(\w+): *(.*)$', content.split('---')[1], re.M))
    headings, code_blocks, buffer, fence = [], [], [], False
    for line in content.split('\n'):
        if line.startswith('```'):
            if fence:
                code_blocks.append('\n'.join(buffer))
                buffer = []
            fence = not fence
            continue
        if fence:
            buffer.append(line)
            continue
        match = re.match(r'^(##+) (.*?)\s*(?:\{#([\w-]+)\})?$', line)
        if match:
            headings.append((len(match.group(1)), match.group(2), match.group(3)))
    return front_matter, headings, code_blocks


def main():
    errors = []
    english = sorted(glob.glob(os.path.join(DOCS, '*.md')))
    german = {os.path.basename(path) for path in glob.glob(os.path.join(DOCS, 'de', '*.md'))}

    for path in english:
        name = os.path.basename(path)
        if name not in german:
            errors.append(f'de/{name}: missing')
            continue
        en_meta, en_headings, en_code = parse(path)
        de_meta, de_headings, de_code = parse(os.path.join(DOCS, 'de', name))

        if en_meta.get('lang') != 'en' or de_meta.get('lang') != 'de':
            errors.append(f'{name}: lang has to be en / de')
        for key in ('permalink', 'nav_order'):
            if en_meta.get(key) != de_meta.get(key):
                errors.append(f'de/{name}: {key} {de_meta.get(key)} differs from {en_meta.get(key)}')
        if [h[0] for h in en_headings] != [h[0] for h in de_headings]:
            errors.append(f'de/{name}: headings differ from {name}')
        else:
            for (_, en_text, en_id), (_, de_text, de_id) in zip(en_headings, de_headings):
                expected = en_id or kramdown_id(en_text)
                if de_id != expected:
                    errors.append(f'de/{name}: heading "{de_text}" needs {{#{expected}}}')
        if en_code != de_code:
            errors.append(f'de/{name}: code blocks differ from {name}')

    for name in sorted(german - {os.path.basename(path) for path in english}):
        errors.append(f'de/{name}: no English page')

    for error in errors:
        print(error)
    print(f'{len(english)} pages checked, {len(errors)} errors')
    return 1 if errors else 0


if __name__ == '__main__':
    sys.exit(main())
