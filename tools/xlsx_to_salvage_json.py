#!/usr/bin/env python3
"""
Convert the Imani salvage workbooks into the JSON the WordPress importer reads.

Run this locally. The JSON it writes contains proprietary auction data and must
never be committed — .gitignore blocks the filename patterns it produces, and
the default output path is outside the repository.

    py tools/xlsx_to_salvage_json.py \
        --scrape   "C:/Users/cc/Downloads/IAA_raw_scrape_07Sep2026.xlsx" \
        --valuation "C:/Users/cc/Downloads/IAA_Pickles_Manheim_Damage_Valuation.xlsx" \
        --scan-date 2026-09-07 \
        --out "C:/Users/cc/imani-salvage-data/salvage-2026-09-07.json"

The rows are emitted POSITIONALLY, exactly as the sheets order them. All mapping,
type coercion and rule evaluation happen in PHP, in code that has unit tests
against it. Doing any of that here would create a second, untested copy of the
mapping — which is how column shifts get baked in.

Requires: openpyxl  (py -m pip install openpyxl)
"""

import argparse
import datetime as dt
import json
import os
import sys

try:
    import openpyxl
except ImportError:
    sys.exit("openpyxl is required:  py -m pip install openpyxl")


# sheet name -> (feed type, number of columns to emit)
FEEDS = {
    "IAA raw scrape":  ("iaa_scrape",    13),
    "IAA lots":        ("iaa_valuation", 23),
    "Pickles salvage": ("pickles",        8),
    "Manheim salvage": ("manheim",        9),
}

STOCK_HEADERS = ("stock #", "stock#", "stock no", "stock")


def cell(v):
    """Normalise a cell to a trimmed string, or None."""
    if v is None:
        return None
    if isinstance(v, dt.datetime):
        return v.strftime("%Y-%m-%d %H:%M:%S")
    if isinstance(v, dt.date):
        return v.strftime("%Y-%m-%d")
    if isinstance(v, float) and v.is_integer():
        v = int(v)
    s = str(v).strip()
    return s if s else None


def find_header_row(ws, max_scan=12):
    """
    Locate the header row by finding the Stock column, rather than assuming a
    fixed row number. Every one of these sheets has a different number of title
    rows above its header, and a hardcoded offset silently shifts every column
    the day a title line is added.
    """
    for r in range(1, min(ws.max_row, max_scan) + 1):
        for c in range(1, min(ws.max_column, 30) + 1):
            v = ws.cell(row=r, column=c).value
            if v and str(v).strip().lower() in STOCK_HEADERS:
                return r, c
    return None, None


def extract(ws, width):
    header_row, stock_col = find_header_row(ws)
    if header_row is None:
        raise SystemExit(
            f'  ! sheet "{ws.title}": no Stock column found in the first 12 rows. '
            f"Refusing to guess the layout."
        )

    headers = [cell(ws.cell(row=header_row, column=c).value) for c in range(1, width + 1)]

    rows = []
    for r in range(header_row + 1, ws.max_row + 1):
        stock = cell(ws.cell(row=r, column=stock_col).value)
        if not stock:
            continue
        rows.append([cell(ws.cell(row=r, column=c).value) for c in range(1, width + 1)])

    return headers, rows, header_row, stock_col


def load(path, out_feeds, report):
    if not path:
        return
    if not os.path.exists(path):
        raise SystemExit(f"  ! not found: {path}")

    wb = openpyxl.load_workbook(path, data_only=True)
    for name in wb.sheetnames:
        if name not in FEEDS:
            continue
        feed_type, width = FEEDS[name]
        ws = wb[name]
        headers, rows, hrow, scol = extract(ws, width)
        out_feeds.append({"type": feed_type, "sheet": name, "headers": headers, "rows": rows})
        report.append(
            f"  {name:<22} -> {feed_type:<14} {len(rows):>4} rows "
            f"(header row {hrow}, stock col {scol}, {width} cols)"
        )


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--scrape", help="IAA raw scrape workbook")
    ap.add_argument("--valuation", help="IAA/Pickles/Manheim valuation workbook")
    ap.add_argument("--scan-date", required=True, help="Y-m-d the scan was taken")
    ap.add_argument("--out", required=True, help="output .json path")
    args = ap.parse_args()

    try:
        dt.datetime.strptime(args.scan_date, "%Y-%m-%d")
    except ValueError:
        raise SystemExit("  ! --scan-date must be YYYY-MM-DD")

    feeds, report = [], []
    load(args.scrape, feeds, report)
    load(args.valuation, feeds, report)

    if not feeds:
        raise SystemExit("  ! no recognised sheets found in the given workbooks")

    payload = {
        "format": "imani-salvage/1",
        "scan_date": args.scan_date,
        "generated_at": dt.datetime.now().isoformat(timespec="seconds"),
        "feeds": feeds,
    }

    out_dir = os.path.dirname(os.path.abspath(args.out))
    if out_dir:
        os.makedirs(out_dir, exist_ok=True)
    with open(args.out, "w", encoding="utf-8") as fh:
        json.dump(payload, fh, ensure_ascii=False, indent=1)

    lots = sum(len(f["rows"]) for f in feeds if f["type"] != "iaa_valuation")
    print("\n".join(report))
    print(f"\n  {lots} lot rows + "
          f"{sum(len(f['rows']) for f in feeds if f['type'] == 'iaa_valuation')} valuation rows")
    print(f"  written: {args.out}")
    print("\n  This file contains proprietary auction data. Do not commit it, "
          "and do not place it inside the theme directory\n"
          "  — the theme rsyncs to the public web root on every deploy.")


if __name__ == "__main__":
    main()
