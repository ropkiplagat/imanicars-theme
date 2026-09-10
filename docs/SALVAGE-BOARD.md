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
| Three books stay disjoint | `books.php` | Brute-forced over 24 years x 3 WOVR values in the suite. Two buyers can never bid against each other. |
| Book bands are formulas | `books.php` | All six edges are `cy - n`, asserted across three calendar years. A gate rejects a literal year in a band function. |
| Books are recomputed on read | board, CSV | The stored columns are a filter index only. A stale index is surfaced in an amber banner, never silently trusted. |
| Estimates never invent a midpoint | `estimate.php` | A range is measured from the end that was missed. A gate greps for midpoint arithmetic. |
| Email sends what is on screen | `ajax.php` | One `build_csv()` feeds both the download and the attachment. A failed send reports the reason; it never claims success. |
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

## The three destination books (10 Sep 2026)

Three buyers, three **disjoint** year bands. Disjoint on purpose: an overlap
means two of Rop's buyers bid against each other and inflate a price they both
pay.

| Book | Band in 2026 | Formula | Extra rule |
|---|---|---|---|
| Imani Car Rentals | 2008-2010 | `cy-18 .. cy-16` | **WOVR N/A only.** Repairable no longer qualifies. |
| Uganda | 2011-2018 | `cy-15 .. cy-8` | 2018 is the only year in the band at 20% levy rather than 50%. |
| Kenya | 2019+ | `cy-7 ..` | KEBS KS 1515. Water fails PVoC separately, at any age. |

Every edge is a formula. `test_books.php` asserts all six across 2026, 2027 and
2030, and asserts disjointness by brute force over 24 years x 3 WOVR values -
because the property Rop chose these bands for is exactly the one a comment
cannot guarantee.

**2011 is contested and is admitted anyway.** URA guidance reads "under 15 years
old from first registration", which excludes it; URA's own environmental levy
band runs 9-15 years, which admits it. Rop assigned it to Uganda **by decision,
not by resolution**. Every 2011 row carries `uganda_boundary_contested_2011` and
the board prints "confirm with URA before committing". The flag survives the
decision on purpose.

**The 2008-2010 band cannot be history-checked.** It sits below every state's
recording threshold (QLD `cy-16`, NSW/WA `cy-15`, VIC `cy-14`), so a clean WOVR
reading proves nothing there. That is not a defect of the band - it is why every
eligible row carries `history_unverified` **and** `ppsr_mandatory`, and why the
board says PPSR is mandatory rather than advisable.

### Why the books are stored *and* recomputed

`book_kenya` / `book_uganda` / `book_rental` are a **filter index**, nothing
more. They let the three checkboxes filter in SQL. But every band is relative to
the calendar year, so a `1` written in 2026 is wrong on 1 January 2027.

- `book_cy` records the year each row's flags were computed against.
- The board and the CSV **recompute** on read, from `IC_Salvage_Books::assess()`,
  using the Australian clock - never UTC, which is still in the previous year for
  ten hours of the Australian 1 January.
- When the stored index disagrees with the live rule, or `book_cy` is behind, the
  board says so in an amber banner and states plainly that the badges are correct
  and the *checkboxes* may not be.

A gate asserts the call sites exist in both directions, because `books.php`
passed all its own tests for days while **nothing called it** - the board was
still filtering on a single Kenya flag. A rule engine no code path reaches is a
document, not a safeguard.

---

## Estimate vs auction price

`estimate.php` compares the pre-bid ceiling against the observed hammer.

- A **range** is measured from the end the price missed. A midpoint is never
  computed - it would be a figure nobody wrote down, and reporting a variance
  against it is inventing a price. A gate greps for midpoint arithmetic.
- An unreadable cell ("TBC", "ask Wilson") yields **null and the original text**,
  so the board can show what it could not read. Never zero.
- A variance needs **both** sides. One known side is not a small variance, it is
  no variance.
- A zero estimate is a real estimate; the percentage is `null`, not `INF`.

The finding this exists to surface, from the 10 Sep Pickles lane: **a statutory
write-off prices as parts, not as a discounted car.** Two statutory Wildtraks sat
$2,400 apart across four model years and 143,000 km, while the repairable of the
same model made 4-6x either. An estimate built down from retail misses a
statutory lot by more than 60%.

---

## Email

The Email control used to be a link that opened the local mail client with a
20-row text summary pasted into the body and no attachment. It was labelled
"Email" and it sent nothing. Rop reported it as "email doesn't send the filtered
results", and he was right.

It now posts to `ic_salvage_email`, which builds the **same CSV the Export button
produces** - one `build_csv()`, not two - and attaches it. The filters travel as
the board's own query string so the file matches the screen exactly.

If `wp_mail` fails it reports the reason from `wp_mail_failed`. It never reports
success on a send that did not happen. **SMTP is still not configured on this
host**, so expect it to fail until that is done; the failure message says so and
points at Export as the fallback.

---

## Password reset, and why it silently fails

Rop asked for the reset to be fixed "so that i can see password". The reset
screen already shows the password in plain text — the problem is that he never
reaches that screen, because the email never arrives.

WordPress's lost-password flow prints **"Check your email for the confirmation
link"** whenever `retrieve_password()` returns, and it returns successfully even
when `wp_mail()` failed outright. On a host with no SMTP — which this one is —
that is a success message for something that never happened. It is the exact
failure this board is built to prevent, shipped by WordPress core.

`mail-status.php` records every `wp_mail` failure with its reason and
contradicts that reassurance on the lost-password screen and in wp-admin.

Two constraints on it:

- **No permanent warning.** A banner that is always on is camouflage; it stops
  being read and then hides the one time it mattered. Nothing shows unless a
  send actually failed, a successful send *deletes* the record, and a failure
  older than 30 days ages out.
- **No secrets, ever.** The reason string is printed on a **public** login
  screen, and PHPMailer quotes the SMTP conversation back in its errors — which
  can carry an AUTH line, an API key, or the reset key for the very email that
  failed. `scrub()` redacts by **shape**, not by label: any token mixing letters
  and digits over ten characters, any URL, any 20+ character blob.

The keyword rule alone was not enough, and the suite proved it twice:

- `key` does **not** match inside `api_key` — underscore is a word
  character — so the first version leaked every `api_key=` value onto the login
  page.
- With `X-Auth-Token: abc123def456`, the keyword rule matched `Auth`, consumed
  `-Token:` as its value, and let the token walk past untouched.

Both are now regression tests. Ordinary diagnostics stay readable: `535`,
`port 25`, `localhost` and the recipient address all survive, because they are
digits-only or letters-only.

**This does not make mail work.** SMTP still has to be configured with a
SendGrid key, and that key goes into an SMTP plugin — never into this theme, this
repository, or a chat message. Until then the board's Email button and every
password reset will fail, and now they will *say* they failed.

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
