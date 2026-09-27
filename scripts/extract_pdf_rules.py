# -*- coding: utf-8 -*-
"""Extract numbered rules from a policy PDF. Prints JSON to stdout."""

import base64
import json
import re
import sys

import pymupdf


BN_DIGITS = str.maketrans("০১২৩৪৫৬৭৮৯", "0123456789")
RULE_START = re.compile(
    r"^\s*([0-9০-৯]{1,3})\s*[\.\)।:\-]\s*(.*)$"
)
NUMBER_ONLY = re.compile(
    r"^\s*([0-9০-৯]{1,3})\s*[\.\)।:\-]?\s*$"
)


def as_number(token: str) -> str | None:
    digits = token.translate(BN_DIGITS)
    if not digits.isdigit():
        return None
    value = int(digits)
    if value < 1 or value > 80:
        return None
    return str(value)


def numbered_rules(text: str):
    rules = []
    lines = [line.strip() for line in text.replace("\u00a0", " ").splitlines()]
    current_no = None
    current_lines = []

    def flush():
        if current_no is None:
            return
        body = re.sub(r"\s+", " ", " ".join(part for part in current_lines if part)).strip(" -\u00a0")
        if len(body) < 8:
            return
        rules.append((current_no, body))

    for line in lines:
        if not line:
            continue
        only = NUMBER_ONLY.match(line)
        if only and as_number(only.group(1)):
            flush()
            current_no = as_number(only.group(1))
            current_lines = []
            continue
        match = RULE_START.match(line)
        if match and as_number(match.group(1)):
            flush()
            current_no = as_number(match.group(1))
            rest = match.group(2).strip()
            current_lines = [rest] if rest else []
            continue
        if current_no is not None:
            current_lines.append(line)

    flush()
    if rules:
        return rules

    # Number and text on the same line, but the PDF did not keep line breaks.
    flat = re.sub(r"\s+", " ", text).strip()
    pieces = re.split(r"(?<=\s)(?=[0-9০-৯]{1,2}\s*[\.\)।]\s+)", " " + flat)
    for piece in pieces:
        match = re.match(r"\s*([0-9০-৯]{1,2})\s*[\.\)।]\s+(.+)", piece)
        if not match or not as_number(match.group(1)):
            continue
        body = match.group(2).strip()
        if len(body) >= 8:
            rules.append((as_number(match.group(1)), body))
    if rules:
        return rules

    return prose_rules(text)


def prose_rules(text: str):
    """Rules that are written as sentences or paragraphs, without numbers."""
    chunks = re.split(r"\n\s*\n|(?<=[।?!])\s+", text.replace("\u00a0", " "))
    rules = []
    bucket = ""
    for chunk in chunks:
        piece = re.sub(r"\s+", " ", chunk).strip(" -\u00a0")
        if not piece:
            continue
        if len(piece) < 25:
            bucket = (bucket + " " + piece).strip()
            continue
        statement = (bucket + " " + piece).strip() if bucket else piece
        bucket = ""
        if len(statement) >= 25:
            rules.append((str(len(rules) + 1), statement))
    if bucket and len(bucket) >= 25:
        rules.append((str(len(rules) + 1), bucket))
    return rules


def document_text(path: str) -> str:
    doc = pymupdf.open(path)
    pages = []
    for page in doc:
        pages.append(page.get_text("text", sort=True))
    doc.close()
    text = "\n".join(pages).replace("\u00a0", " ").replace("\r", "\n")
    return text.strip()


def preamble(text: str) -> str:
    lines = []
    for raw in text.splitlines():
        line = raw.strip()
        if not line:
            continue
        if RULE_START.match(line):
            break
        lines.append(line)
    return " ".join(lines)


def find_when(header: str) -> str:
    match = re.search(r"(\d{1,2}[./-]\d{1,2}[./-]\d{2,4}|\d{4})", header)
    return match.group(1) if match else "উল্লেখ নেই"


def find_who(header: str) -> str:
    for word in ("অনুমোদিত", "প্রণীত", "ইস্যু", "জারি", "ব্যবস্থাপনা", "পরিচালনা পর্ষদ", "নির্বাহী পরিচালক"):
        if word in header:
            return word
    return "উল্লেখ নেই"


def title_of(statement: str) -> str:
    words = statement.split()
    title = " ".join(words[:10]).rstrip("।,; ")
    return title or "Rule"


def main() -> int:
    sys.stdout.reconfigure(encoding="utf-8")
    if len(sys.argv) < 2:
        print(json.dumps({"error": "missing pdf path"}, ensure_ascii=False))
        return 1

    path = sys.argv[1]
    source = sys.argv[2] if len(sys.argv) > 2 else "PDF"
    source_label = re.sub(r"\.pdf$", "", source, flags=re.I).replace("_", " ").strip() or "PDF"

    try:
        text = document_text(path)
    except Exception as error:
        print(json.dumps({"error": f"Could not open the PDF: {error}"}, ensure_ascii=False))
        return 1
    found = numbered_rules(text)
    header = preamble(text)
    where = source_label
    when = find_when(header)
    who = find_who(header)

    rules = []
    for number, statement in found:
        rules.append({
            "title": title_of(statement),
            "statement": statement,
            "article": number,
            "where": where,
            "when": when,
            "who": who,
        })

    pages = 0
    try:
        opened = pymupdf.open(path)
        pages = opened.page_count
        opened.close()
    except Exception:
        pages = 0

    payload = {"rules": rules, "text": text[:48000], "chars": len(text), "page_count": pages}
    letters = len(re.findall(r"[A-Za-z\u0980-\u09FF]", text))
    if pages < 1 or letters < pages * 350 or not rules:
        payload["pages"] = raster_pages(path)

    print(json.dumps(payload, ensure_ascii=False))
    return 0


def raster_pages(path: str) -> list[list[str]]:
    """Each page as the top half and the bottom half, so the lower rules are visible."""
    pages = []
    doc = pymupdf.open(path)
    zoom = pymupdf.Matrix(2.4, 2.4)
    try:
        for index, page in enumerate(doc):
            if index >= 12:
                break
            rect = page.rect
            height = rect.height
            if height <= 520:
                bands = [(rect.y0, rect.y1)]
            else:
                mid = rect.y0 + height * 0.56
                bands = [
                    (rect.y0, mid),
                    (rect.y0 + height * 0.44, rect.y1),
                ]
            slices = []
            for top, bottom in bands:
                clip = pymupdf.Rect(rect.x0, top, rect.x1, bottom)
                pix = page.get_pixmap(matrix=zoom, clip=clip, alpha=False)
                slices.append(base64.b64encode(pix.tobytes("jpeg", jpg_quality=84)).decode("ascii"))
            pages.append(slices)
    finally:
        doc.close()
    return pages


if __name__ == "__main__":
    raise SystemExit(main())
