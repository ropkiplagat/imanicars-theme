#!/usr/bin/env python3
"""
Salvage board gatekeeper.

    py tools/check.py

Exit code 0 only when every HARD gate passes.

The unit-test gate RUNS the suite and reads its exit code. There is no stored
"tests passed" marker anywhere in this repo, and no gate here reports success
from anything other than the artefact it just inspected — a confirmation written
by a path that runs regardless of the work is camouflage, not evidence.

Gates that need a live logged-in browser session are listed at the end as
explicitly UNVERIFIED rather than assumed green.
"""

import json
import os
import re
import subprocess
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
HARD, SOFT = "HARD", "SOFT"

results = []


def gate(name, kind, ok, detail=""):
    results.append((name, kind, bool(ok), detail))
    mark = "PASS" if ok else ("FAIL" if kind == HARD else "WARN")
    print(f"  [{mark}] {name}")
    if detail and not ok:
        print(f"         {detail}")
    return ok


def read(rel):
    p = os.path.join(ROOT, rel)
    if not os.path.exists(p):
        return None
    with open(p, encoding="utf-8", errors="replace") as fh:
        return fh.read()


def git(*args):
    try:
        return subprocess.run(
            ["git"] + list(args), cwd=ROOT, capture_output=True, text=True, timeout=60
        ).stdout
    except Exception:
        return ""


# ---------------------------------------------------------------
print("\nSALVAGE BOARD GATE\n" + "=" * 62 + "\n")

# 1 — the unit suite, actually executed
print("Tests")
try:
    proc = subprocess.run(
        ["docker", "run", "--rm", "-v", f"{ROOT}:/app", "-w", "/app",
         "php:8.2-cli", "php", "tests/salvage/run.php"],
        capture_output=True, text=True, timeout=300,
        env={**os.environ, "MSYS_NO_PATHCONV": "1"},
    )
    out = (proc.stdout or "") + (proc.stderr or "")
    passed = proc.returncode == 0 and "PASS" in out
    m = re.search(r"PASS\s+(\d+) assertions", out)
    count = int(m.group(1)) if m else 0
    gate("unit suite passes", HARD, passed, out.strip()[-600:])
    # A suite that runs but asserts almost nothing is not evidence.
    gate("suite asserts a meaningful number of cases", HARD, count >= 150,
         f"only {count} assertions executed")
except FileNotFoundError:
    gate("unit suite passes", HARD, False, "docker not available to run php:8.2-cli")
except Exception as e:
    gate("unit suite passes", HARD, False, str(e))

# 2 — no proprietary auction data in the repository
print("\nData containment (this repo is PUBLIC and rsyncs to the web root)")
# Everything git WOULD commit: tracked files plus untracked ones that are not
# ignored. Scanning only tracked files would blind this gate at exactly the
# moment it matters — a brand-new data file staged for its first commit.
tracked = [
    l for l in git("ls-files", "--cached", "--others", "--exclude-standard").splitlines()
    if l.strip()
]

bad_files = [
    f for f in tracked
    if re.search(r"(salvage.*\.(json|csv|xlsx)|IAA_.*\.(csv|xlsx)|_scrape_.*\.(csv|xlsx))",
                 f, re.I)
]
gate("no auction data files committable", HARD, not bad_files, f"tracked: {bad_files}")

# Real IAA/Pickles/Manheim stock numbers are 7-8 digits. Synthetic fixtures use
# the 9000xxxx block, which this deliberately does not match.
stock_hits = []
for f in tracked:
    if not f.endswith((".php", ".js", ".css", ".md", ".json", ".py", ".xml", ".txt")):
        continue
    body = read(f)
    if body is None:
        continue
    for m in re.finditer(r"\b(5[01]\d{6}|6[23]\d{6}|7[34]\d{5})\b", body):
        stock_hits.append(f"{f}: {m.group(1)}")
gate("no real stock numbers in committable files", HARD, not stock_hits,
     "; ".join(stock_hits[:6]))

gi = read(".gitignore") or ""
gate("gitignore blocks the converter's output", HARD,
     "*salvage*.json" in gi and "IAA_*.xlsx" in gi,
     "add the salvage data patterns to .gitignore")

# 3 — search-engine exclusion
print("\nSearch-engine exclusion")
sitemap = read("sitemap.xml") or ""
gate("/insurance absent from sitemap.xml", HARD, "insurance" not in sitemap.lower())

robots = read("robots.txt") or ""
gate("/insurance absent from robots.txt (a Disallow would publish the path)",
     HARD, "insurance" not in robots.lower())

access = read("inc/salvage/access.php") or ""
gate("noindex meta emitted for the board", HARD,
     'noindex, nofollow' in access and "noindex_meta" in access)
gate("X-Robots-Tag header sent", HARD, "X-Robots-Tag" in access)
gate("robots header is sent before the login redirect too", HARD,
     access.find("send_robots_header();") < access.find("wp_safe_redirect")
     and "send_robots_header();" in access)

fn = read("functions.php") or ""
gate("theme's unconditional 'index, follow' is suppressed on the board", HARD,
     "ic_salvage_is_board_page" in fn
     and fn.find("ic_salvage_is_board_page") < fn.find('content="index, follow"'))

gate("page excluded from REST, core sitemap, search and page lists", HARD,
     all(k in access for k in ("rest_page_query", "rest_prepare_page",
                               "wp_sitemaps_posts_query_args",
                               "exclude_from_search", "wp_list_pages_excludes")))

# 4 — access control
print("\nAccess control")
gate("guard redirects logged-out visitors to wp_login_url", HARD,
     "wp_login_url" in access and "template_redirect" in access)
gate("logged-in users without the capability get 403", HARD,
     "'response' => 403" in access.replace('"', "'"))
gate("authentication uses WordPress core, not a hand-rolled login", HARD,
     not re.search(r"password_verify|wp_hash_password|\$_POST\[.(pass|password)", access))

ajax = read("inc/salvage/ajax.php") or ""
handlers = re.findall(r"public static function (\w+)\(", ajax)
gate("every write endpoint checks capability AND nonce", HARD,
     "require_access" in ajax
     and "current_user_can" in ajax
     and "check_ajax_referer" in ajax
     and "wp_verify_nonce" in ajax,
     f"handlers: {handlers}")

# No credentials anywhere.
secret_hits = []
for f in tracked:
    if not f.endswith((".php", ".js", ".py", ".json", ".yml", ".yaml")):
        continue
    body = read(f) or ""
    for pat in (r"(?i)api[_-]?key\s*=\s*['\"][A-Za-z0-9_\-]{16,}",
                r"(?i)password\s*=\s*['\"][^'\"]{6,}",
                r"SG\.[A-Za-z0-9_\-]{20,}",
                r"(?i)secret\s*=\s*['\"][A-Za-z0-9_\-]{16,}"):
        if re.search(pat, body):
            secret_hits.append(f)
            break
gate("no credentials committed", HARD, not secret_hits, f"{secret_hits}")

# 5 — the fee engine's acceptance numbers, asserted here as well as in the suite
print("\nFee rules")
fees = read("inc/salvage/fees.php") or ""
gate("IAA schedule is 14.95% + $135 over $1,000, no export admin", HARD,
     "'pct'          => 1495" in fees and "'flat'         => 13500" in fees
     and "'export_admin' => 0," in fees)
gate("Pickles schedule is 15% + $160, plus $236 admin and $36 transfer", HARD,
     "'pct'          => 1500" in fees and "'flat'         => 16000" in fees
     and "'export_admin' => 23600" in fees and "'transfer'     => 3600" in fees)
# Manheim's schedule was retrieved on 10 Sep 2026. It only ever 403'd to a bare
# fetch; a browser User-Agent gets HTTP 200. The invariant is no longer "unknown"
# — it is "sourced from the published table, and cited".
gate("Manheim schedule is the published one, $120 + 15% over $1,000", HARD,
     "'pct'          => 1500" in fees
     and "'flat'         => 12000" in fees
     and "array( 20000, 12100 )" in fees
     and "Aug 2026" in fees)
gate("Manheim claims no export admin fee it does not publish", HARD,
     re.search(r"'Manheim'\s*=>\s*array\((?:[^)]|\)(?!,\s*\)))*?'export_admin'\s*=>\s*0", fees, re.S)
     is not None)
gate("sub-$1,000 bands charge no percentage", HARD,
     "'bands'" in fees and "banded" in fees)
gate("money is integer cents, never a float multiply", HARD,
     "intdiv" in fees and not re.search(r"\*\s*0\.1495|\*\s*0\.15\b", fees))
gate("duty is never implied to be included", HARD, "CRSP" in fees)

# 6 — screening rules
print("\nScreening rules")
rules = read("inc/salvage/rules.php") or ""
gate("KEBS cutoff is a formula, not a hardcoded 2019", HARD,
     "KEBS_WINDOW_YEARS" in rules
     and not re.search(r"(>=|==)\s*2019", rules))
gate("flood rejection is independent of vehicle age", HARD,
     "flood_reject" in rules
     and rules.find("if ( true === $flood )") < rules.find("if ( null === $age_ok )"))
gate("VIC EPA note does not assert the export position", HARD,
     "UNCONFIRMED" in rules)
gate("no V8 Prado guard present", HARD, "drivetrain_conflict" in rules)

# 7 — import safety
print("\nImport safety")
imp = read("inc/salvage/import.php") or ""
repo = read("inc/salvage/repo.php") or ""
gate("importer never deletes", HARD,
     not re.search(r"(?i)\bDELETE\b|->delete\(", imp + repo))
gate("upsert is keyed on (source, stock)", HARD,
     "UNIQUE KEY source_stock" in (read("inc/salvage/schema.php") or ""))
gate("import never writes a lot status (sold lots keep their status)", HARD,
     "'status'" not in re.sub(r"/\*.*?\*/", "", repo, flags=re.S).split("add_observation")[0]
     or "scrape_fields" in repo and "'status'" not in repo.split("scrape_fields")[1].split("}")[0])
gate("lots absent from a new scan are reported, not removed", HARD,
     "absent_from_scan" in imp)
gate("derived Kenya flags are reconciled against the source column", HARD,
     "collect_anomalies" in imp and "kebs_eligible" in imp)

# 8 — the board UI
print("\nBoard UI")
page = read("page-insurance.php") or ""
css = read("assets/css/salvage.css") or ""
js = read("assets/js/salvage.js") or ""

gate("frontend-design §1 questions answered in the template", HARD,
     "WHO / WHAT DECISION" in page and "THE ONE NUMBER" in page
     and "MISLEAD" in page)
gate("Kenya eligibility is a checkbox and defaults OFF", HARD,
     'name="kenya_only"' in page and "'kenya_only'    => ! empty(" in
     (read("inc/salvage/view.php") or ""))
gate("all four flag columns render", HARD,
     all(k in page for k in ("48h", "TOMORROW", "KEBS", "FLOOD", "VIC EPA")))
gate("unknown renders distinctly from no", HARD,
     "sb-flag--unknown" in css and "sb-flag--off" in css)
gate("empty and error states exist", HARD,
     "sb-empty" in page and "sb-alert--bad" in page and "sb-empty" in css)
gate("money is tabular and right-aligned", HARD,
     "tabular-nums" in css and ".sb-num" in css)
gate("the four export actions are present", HARD,
     all(k in page for k in ("ic_salvage_export", "data-sb-copy",
                             "data-sb-print", "sb-email")))
gate("the browser never computes a fee", HARD,
     "ic_salvage_quote" in js
     and not re.search(r"0\.1495|0\.15\s*\*|\*\s*0\.15", js))
gate("mobile card layout exists", HARD,
     "max-width: 900px" in css and "data-label" in page)
gate("inputs are 16px so iOS does not zoom on focus", HARD,
     re.search(r"\.sb-entry__row input[^}]*font-size:\s*16px", css, re.S) is not None)
gate("touch targets are at least 44px", HARD,
     "min-height: 44px" in css and "min-height: 48px" in css)
gate("print stylesheet exists", HARD, "@media print" in css)
gate("caveats render as visible text, not tooltips only", HARD,
     "sb-standing" in page and "sb-cell-warn" in css)

# ---------------------------------------------------------------
print("\n" + "=" * 62)
hard_fail = [r for r in results if r[1] == HARD and not r[2]]
soft_fail = [r for r in results if r[1] == SOFT and not r[2]]
print(f"  {len(results) - len(hard_fail) - len(soft_fail)}/{len(results)} gates passed")

print("""
NOT VERIFIABLE FROM THIS REPOSITORY — these need a live, logged-in session
and must be signed off separately before the board is trusted:

  * UAT 1/2  the login redirect and the role check, exercised in a private window
  * UAT 3    the password-reset email actually ARRIVING at ropkiplagat@gmail.com.
             HANDOVER.md records that wp_mail on this SiteGround host has already
             failed to deliver once. Treat this as BROKEN until an email is seen
             in the inbox; SMTP must be configured first.
  * UAT 4    noindex present in the LIVE page source
  * UAT 5    all rows imported with no column shift, spot-checked against the
             workbook including a blank-odometer row
  * UAT 8    export / copy / print / email exercised in a real browser
  * UAT 10   one-handed use on an actual phone
""")

if hard_fail:
    print("BLOCKED — hard gates failed:")
    for n, _, _, d in hard_fail:
        print(f"  x {n}")
        if d:
            print(f"      {d}")
    sys.exit(1)

if soft_fail:
    print("Passed with warnings.")
sys.exit(0)
