# Salvage board — /insurance

Private, login-gated auction board for Imani Car Sales. Built 8 September 2026.

Run the gate before shipping anything here:

```
py tools/check.py          # 44 hard gates; runs the PHP suite as gate #1
```

---

## What the brief said, and what was actually on disk

The build brief described a source file and a dataset that do not exist. Reality
won; the schema was built to the brief's column list as a superset, and the real
feeds map into it with honest NULLs where a source publishes nothing.

| Brief | Reality |
|---|---|
| `imani_salvage_scan_2026-09-08.xlsx` | No such file. Two workbooks in `~/Downloads`, dated **7 Sep**. |
| Sheets *Raw Scan*, *Price Tracker*, *Method & Rules* | None exist. Actual sheets: `IAA raw scrape`, `IAA lots`, `Pickles salvage`, `Manheim salvage`, `Parts costing`, `Kenya export screening`. |
| **343 lots** | **132 lots** — 65 IAA + 57 Pickles + 10 Manheim. |
| A three-week price tracker "sits behind it" | **Does not exist yet.** No observation data anywhere. The board *creates* the tracker; it is empty until Rop starts recording. |
| Skills `website-cloner`, `pm-agent`, `seo-ranker` | Not installed. Only `frontend-design` exists, and it was read before any markup. |

The 12-row `IAA lots` sheet is **enrichment**, not a fourth feed: repair
estimates, verdicts and pre-bid figures keyed by stock number onto 12 of the 65
IAA lots. It patches; it never creates a lot.

### Columns the brief lists that no source publishes

`SaleEvent`, `Sells_Next_48h`, `Sells_Tomorrow`, `Damage_Published` are derived.
`SaleDateTime_AEST` exists **only** for Pickles and Manheim — IAA publishes no
sale time at all, so 65 of 132 lots have none. `AirBags`, `StartCode`,
`ComplianceDate` exist only for the 12 valued lots. All render as an em dash with
an explanation, never as a zero or a "No".

---

## The decision that changed the architecture

**The theme repository is PUBLIC** (`github.com/ropkiplagat/imanicars-theme`)
**and rsyncs to the live web root on every push to `main`.**

Non-negotiable #1 is that this data must never reach a rival bidder. Committing
the dataset would have published 132 lots, valuations and bidding verdicts on
GitHub — a worse leak than any indexing failure, and the exact thing the login
gate exists to prevent.

So: **code lives in the repo, data never does.**

- Lot data lives only in the `wp_ic_salvage_*` tables.
- Import is an upload through wp-admin. The JSON is consumed from PHP's temp
  directory and never written into the theme directory — anything in the theme
  directory is world-fetchable under `/wp-content/themes/…` after a deploy.
- `.gitignore` blocks the converter's output patterns.
- Two hard gates enforce it: no data files committable, and no real stock numbers
  in any committable file. Test fixtures are synthetic (`9000xxxx` block).

**`/insurance` is deliberately NOT in `robots.txt`.** robots.txt is world
readable, so a `Disallow: /insurance/` line would publish the very path being
protected. The page is unreachable without a login and carries `noindex` plus an
`X-Robots-Tag` header; advertising its path would be a net loss. A gate asserts
its absence from both robots.txt and the sitemap.

---

## Rules that are enforced in code, not just documented

| Rule | Where | Guard |
|---|---|---|
| KEBS age window | `rules.php` | A **formula** (`year - 7`), not a hardcoded 2019. Tested across 2025–2028; 2019 is asserted *inside* the window for 2026. A hardcoded cutoff passes a 2019-only test and is silently wrong on 1 Jan 2027 — the same off-by-one that "8 years or newer" has produced before. |
| Flood fails PVoC at any age | `rules.php` | Checked **before** age and independently of it, so a 2025 flood car is still rejected and the reason given is water, not age. |
| VIC statutory → EPA licence | `rules.php` | Flagged. The note explicitly marks the export-vs-dismantling question **UNCONFIRMED** rather than asserting a legal position. |
| Duty is on CRSP | `fees.php`, board, CSV | Every cost is labelled "before duty" with the CRSP basis as adjacent text. |
| Never invent a price | `fees.php` | Manheim returns NULL + the 403 reason. Never an estimate. |
| No V8 Prado | `rules.php` | A row claiming a V8 or 4.5 Prado is flagged as a mis-scrape at import. |
| Never delete a sold lot | `repo.php`, `import.php` | The importer contains no DELETE and never writes a status. Status derives only from observations, so a re-import cannot resurrect a sold lot as Live. Lots absent from a new scan are *reported*, not removed. |

### Buyer fees

Integer cents throughout — never a float. Acceptance numbers are asserted in the
suite **and** as a static gate:

- IAA $8,500 → **$1,405.75** (14.95% + $135)
- Pickles $8,500 → **$1,707.00** (15% + $160 + $236 export admin + $36 transfer)
- Manheim → **not found**, in red, with the 403 reason

At or below the $1,000 threshold the flat component is not published, so the
quote is marked **partial** and says what is missing rather than assuming zero.

---

## Three-state rendering

Every flag has three renderings, not two: true, false, and **unknown**. Unknown
gets a dashed outline and an explanation, visually distinct from both a pass and
a fail.

This is the single most important UI decision on the page. "Sells tomorrow: No"
on an IAA lot would be a lie — IAA publishes no sale times, so the honest answer
is "we don't know". Likewise Pickles publishes no damage codes, which makes every
Pickles lot's Kenya eligibility **unknown**, not eligible: water damage cannot be
ruled out on a lot whose damage was never published. The "Kenya-eligible only"
filter therefore admits only lots *proven* inside the window, and the empty state
says so.

---

## Testing

`tests/salvage/` — 219 assertions over the four pure-logic modules, runnable on a
bare `php:8.2-cli` container with no composer install:

```
docker run --rm -v "C:/Users/cc/imani-cars-theme:/app" -w /app php:8.2-cli php tests/salvage/run.php
```

The runner's exit code comes from the assertion counters. A file that loads but
asserts nothing **fails**, and a zero-assertion run fails — a green result has to
be evidence that the checks executed.

The gate was break-tested in both directions. Breaking a fee constant fails both
the suite and the static gate; adding `/insurance` to the sitemap fails; planting
a real stock number fails. That last one **initially passed when it should have
failed** — the containment gate scanned only `git ls-files`, so a brand-new
untracked file was invisible to it, which is exactly the moment the gate matters.
Now fixed to scan everything git would commit.

---

## Still unverified — do not treat the board as signed off

These need a live, logged-in session and cannot be checked from the repo:

1. **UAT 3 — the password-reset email. Assume BROKEN.** `HANDOVER.md` already
   records that WP mail on this SiteGround host silently failed to deliver the
   `ropkiplagat` account notification (Gmail searched `in:anywhere`, nothing
   arrived) even though `info@` and `rentals@imanicars.com` receive normal mail.
   **SMTP must be configured and a reset link seen in the inbox before this is
   ticked.** A reset link that never arrives is the same as having no reset.
2. UAT 1/2 — login redirect and role check in a private window.
3. UAT 4 — `noindex` present in the live page source.
4. UAT 5 — 132 rows imported with no column shift, spot-checked against the
   workbook including a blank-odometer row (Pickles and Manheim both carry `?`).
5. UAT 8 — export / copy / print / email in a real browser.
6. UAT 10 — one-handed use on an actual phone.

The deploy pipeline is also flaky: 2 of 4 historical runs failed on SiteGround
SSH timeouts. Confirm any deploy against the server, never against the push.

---

## Running an import

```
py tools/xlsx_to_salvage_json.py \
    --scrape    "C:/Users/cc/Downloads/IAA_raw_scrape_07Sep2026.xlsx" \
    --valuation "C:/Users/cc/Downloads/IAA_Pickles_Manheim_Damage_Valuation.xlsx" \
    --scan-date 2026-09-07 \
    --out       "C:/Users/cc/imani-salvage-data/salvage-2026-09-07.json"
```

Then **Tools → Salvage import** in wp-admin. The output path is outside the repo
on purpose. The converter locates each sheet's header by finding its Stock
column rather than assuming a row number — the four sheets have different numbers
of title rows above their headers, and a hardcoded offset shifts every column the
day someone adds a title line.

The import report is produced by the import itself: per-feed counts, rows that
were skipped, valuation rows with no matching lot, and any lot where the derived
Kenya flag **disagrees with the source's own column**. That reconciliation is the
check that catches either a bad rule or a bad scrape.

## Out of scope

The customer-facing computer-vision damage assessment tool is Phase 2 and was not
started.
