# Imani Cars — session handover

Chrome audits, Code implements. The browser session has no repo and loses
scrollback; this file is the shared state. Update it at the end of any session.

## Live facts (verified, with method)

| Fact | Method |
|---|---|
| imanicars.com is WordPress | `wp-login.php` 200, `/wp-includes/` assets, admin title |
| Theme deploys by GitHub Actions rsync to SiteGround on push to `main` | `.github/workflows`, run 33296464916 = success |
| `single.php` renders car pages (NOT Elementor) | Editing it changed the live page; Unsplash thumbs disappeared |
| Users: exactly 2, both third-party admins | `users.php` — `consultant`/San Soft (15 posts), `Syed` (0 posts) |
| Rop has no account on the site | same |
| `Advanced WP Reset` active, claims a completed DB reset | dashboard notice, timing UNKNOWN |

## Deployed changes

**f31161b** — "Show real vehicle photos instead of hardcoded stock images"
(5 files, +352/-10). Deployed and verified on the server:

- `assets/js/admin-gallery.js` → HTTP 200, 1955 bytes (file did not exist before)
- `assets/css/main.css` → contains `.ic-single-gallery__notice`, Last-Modified 2026-08-30 06:15:08 GMT
- `/cars/2022-ford-ranger-xlt-4x4-double-cab/` → zero Unsplash URLs (was 4 hardcoded `ic_unsplash()` seeds)

What it does: `_ic_gallery` meta (ordered attachment IDs) + a Vehicle Photos
meta box; `ic_get_car_gallery()` reads it, featured image leads. Falls back to
stock images ONLY when a vehicle has zero photos, and says so in visible text.
Thumbnail strip hidden when there is one photo.

## Known problems, not yet fixed

1. **Demo images show the wrong subject entirely.** The Ranger's featured image
   is a workshop close-up with no vehicle in frame. These are real attachments
   in `wp-content/uploads/`, so the zero-photo disclosure does NOT catch them.
   Only real photos fix this.
2. **Public "List Your Car Free" flow saves nothing.** `page-create-listing.php`
   previews photos via `FileReader` only; the publish button redirects to
   `/seller-dashboard/?published=1` with no POST. `functions.php` has AJAX
   handlers for `ic_enquiry` and `ic_dealer_signup` only — no upload, no listing
   creation. It shows success regardless of outcome.
3. **Stock-photo disclosure is untested in the wild** — needs a photoless listing.
4. **Advanced WP Reset** is active on a live site and claims a completed reset.
   Timing unestablished. If recent, "form saves nothing" and "database was wiped"
   look identical from the front end.

## Open decisions — Rop's, not an agent's

- Who are San Soft (`smm.sansoft@gmail.com`) and Syed (`support@techcure.io`)?
  If contractors, ask them for an owner account. If unrecognised on a domain Rop
  owns, start at SiteGround, not WordPress.
- Do NOT repoint `consultant`'s email at Rop's Gmail. That captures a
  contractor's password resets and locks them out. Create a new admin instead —
  and Rop creates it, not an agent acting from inside someone else's session.

## Account state (2026-08-30)

- `ropkiplagat` / ropkiplagat@gmail.com / Administrator CREATED and verified in `users.php` (3 users, all admin).
- **The WP notification email never arrived.** Gmail searched `in:anywhere newer_than:2d` for
  "Login Details" / wordpress / imanicars -- nothing. WP mail on this host is not delivering to
  Gmail, even though info@ and rentals@imanicars.com receive normal business mail.
- The generated password was shown on the Add User form and is no longer retrievable (page
  navigated away); it was never recorded. Rop must set one via Users -> ropkiplagat -> Edit.
- **Do NOT delete `consultant` or `Syed` until Rop has logged in as `ropkiplagat` and confirmed it.**
  They are the only other admins; WP also refuses to let an account delete itself, so `consultant`
  cannot be removed from within its own session. `consultant` owns 15 posts needing reassignment.

## Deploy pipeline is flaky

Run 33296938050 (7cae815) FAILED: `ssh: connect to host *** port 18765: Connection timed out`,
rsync exit 255. Transient SiteGround SSH reachability, not a code fault. 2 of 4 historical runs
failed the same way. f31161b did land and is verified on the server; 7cae815 (docs only) did not.
Always confirm a deploy against the server, never against the push.

## Listings created 2026-08-30

Media (first real uploads on the site): 93741 corolla-e120-01-web, 93742 mazda-323-01-web,
93749-93758 mazda6-01..10.

Vehicles, all DRAFT:
- 93747 "Mazda 323"   - featured 93742 (front 3/4)
- 93748 "Toyota Corolla" - featured 93741 (REAR shot - see rule below)
- 93759 "Mazda 6"     - featured 93749 (front 3/4), _ic_gallery = 93750,93751,93752,93753,
                        93754,93755,93756,93757,93758

Taxonomies on all three: vehicle_make (Mazda 497 / Toyota 495), vehicle_body (Sedan 522),
vehicle_city (Melbourne 518), vehicle_condition (Used 523).

**Main photo must show the car FACING FORWARD** (Rop, 2026-08-30). Mazda 323 and Mazda 6 comply.
Toyota Corolla 93748 does NOT - its only photo is a rear shot. A front photo still needs sourcing.

The Mazda 6 listing is the first real exercise of the gallery: ten-angle auction set, thumbnail
strip renders, click-to-swap works.

## Subaru Forester - Pickles branding removed (2026-08-30)

Vehicle 93765 "Subaru Forester", DRAFT. Featured 93760 (front 3/4). _ic_gallery =
93761,93762,93763,93764. Media 93760-93764. Terms added: vehicle_make Subaru 505,
vehicle_body SUV 524.

These shots carry NO visible rego - Pickles covers the plate with its own branded card - so what
needed hiding was the auction house's mark, not a number. All five cards masked. forester-04 also
carried a BAKER MOTORS sticker on the rear glass; another dealer's name has no place on an Imani
listing, so that is masked too.

Windscreen auction lot tags remain on 01/02/03. Small and illegible at listing size; masking them
was not asked for, so they are left. Revisit if the listings go up at larger sizes.

## Toyota RAV4 - the right kind of source (2026-08-30)

Vehicle 93771 "Toyota RAV4", DRAFT. Featured 93769, _ic_gallery = 93770. Media 93769-93770.
_ic_year = 2018 - the ONLY listing with a year, because the filename asserts it rather than it
being guessed off body shape.

These came from Drive, not Google Photos: spyne.ai processed dealership photos
(IMANI-CAR-SALES-TOYOTA-RAV4-2018G/H-798x466.jpg, 1920x1080, shared from priyanka.sambyal@spyne.ai,
folder 1VPNcg3prGJQ81ybv4pAvOLxl0-qF2q8K). Background replaced, Imani Car Sales logo already on the
studio wall, no plates, no auction branding. NOTHING needed masking. This is the format every
listing should use - see Rop: "use photos after registration", not auction-lot shots.

Only TWO such photos exist in Drive. A third file in that folder is a Mazda wheel close-up, not
the RAV4.

## Drive folder "Imani Cars - Listing Photos" is EMPTY

Folder 1OxDTPWyNhdHdIAhaRaRdDGtKApsqTIXQ, created 2026-08-30 05:53 by the browser session. Zero
files in it. Rop believed the photos were there. The folder exists; the work behind it does not -
the same success-artefact shape as ADR-014 and the Nicole call bug. Verify a folder has FILES, not
just that it was created.

## These listings are DEMO content (Rop, 2026-08-30)

"all photos are demo ones - use what you can to help me show car dealerships". The site is a
demo shown to prospective dealership clients, not live stock sold to consumers. That retires the
earlier concern about invented odometer/price figures. It does NOT retire plate masking, EXIF
stripping or auction-brand removal - those are about looking professional and not publishing
third-party marks. Rule from Rop: "where one photo exists, use it" - a single-photo listing is fine.

## WhatsApp images: checked, no cars

107 WhatsApp images already sat in C:/Users/cc/Downloads. Contact-sheeted all of them: FXPulse
trading alerts, PLC/meter tender documents, family photos, payment receipts, Imani Wellness and
MarkAe brochures. ZERO car photos. Do not re-search this source.

## Mazda 6 - two mask treatments, and why

Final media: featured 93772 (01), gallery 93750,93751,93773,93775,93754,93774,93756,93757,93758.
Marks removed: VIC plate 2AE 5XK, auction paperwork on the dash (01/02/10), lot sticker (06).

Treatment depends on the backdrop:
  - Behind glass or in a dark bumper recess -> solid dark block. Reads as deliberate redaction.
  - Small sticker on clean bodywork -> colour-matched fill sampled from the surrounding panel.
    A black square on a car's rear quarter looks worse than the sticker, which defeats the point
    on a site meant to impress dealerships.
  - mazda6-07's sticker is deliberately LEFT: the panel has a gradient there, so the fill read as
    a pale rectangle, more conspicuous than the round mark it replaced. Not an oversight.

## READY BUT NOT UPLOADED - permission blocked (2026-08-30)

Two more cars are processed and staged on disk. The upload was refused by the Claude Code auto-mode
permission classifier, as was publishing. Files are in C:/Users/cc/imani-cars-photos/new2/:

  yaris-01.jpg    - blue Toyota Yaris hatch, front 3/4. ONE photo (Rop: "where one photo exists,
                    use it"). No plate fitted, nothing to mask.
  sonata-01.jpg   - white Hyundai Sonata, front 3/4  <- use as featured
  sonata-03.jpg   - Sonata rear 3/4
  sonata-02.jpg   - Sonata rear 3/4, other side

Neither car needs masking: the plate recesses are empty on every shot. Source was a Dec 2022
auction-app screenshot set, but cropped to the photo with no visible branding.

Terms still needed: vehicle_make "Hyundai"; vehicle_body "Hatch" for the Yaris (Sedan 522 exists
for the Sonata; SUV 524 and Sedan 522 already created).

## Everything is still DRAFT

Publishing was blocked by the same classifier. The five existing listings (93747 Mazda 323,
93748 Toyota Corolla, 93759 Mazda 6, 93765 Subaru Forester, 93771 Toyota RAV4) are complete and
waiting. To publish by hand: Vehicles -> select -> Bulk actions -> Edit -> Status: Published.

Also still present: two accidental duplicate drafts 93745 "Mazda 323 Sedan" and 93746 "Toyota
Corolla Sedan", created by an API call that reported as blocked but had already written. Deleting
them was refused too.

## Photo handling - what bit, and what to avoid

Source photos live in C:/Users/cc/imani-cars-photos (outside this repo).

Plates masked so far: each car's own, plus 1GT 1NV - a THIRD car visible through the Mazda 323's
windscreen - plus VIC 2AE 5XK across 6 of the 10 Mazda 6 shots.

Two gotchas, both caught only by looking:
1. A mask sized to the visible plate text CLIPPED the tilted plates in mazda6-01 and -10, leaving
   a readable corner. Always re-crop the masked region and inspect it before uploading.
2. EXIF carries GPS. The Corolla photo held 37d57'10.7"S 145d09'17.1"E (SE Melbourne) - publishing
   it unstripped would have put a home address on a public listing. Strip metadata on every file.

The Google Photos "car" search interleaves vehicle photography with Pickles auction and payment
screenshots carrying Rop's financial data (a -$13,943.80 transfer, a partial card number). Do NOT
range-select a date block and upload it; select deliberately.

Still unpulled, all AU: white Subaru Forester (2 Sept 2024, Pickles, multi-angle incl. front),
silver Toyota RAV4 (19 Dec 2022), dark Mazda sedan (14 Dec 2022).

The Mazda 6 set is auction-house photography - Rop presumably owns the car, not the photos. That
is different from the Gumtree screenshots, which show another seller's car entirely: excluded.

Left DRAFT deliberately: no year, no odometer, no price. Rop said to leave KMs out; year is only a
body-shape range (Mazda 323 BJ ~1998-2003, Corolla E120 ~2001-2006, Mazda 6 GJ ~2013-2016) and a
wrong year on a real listing is a misrepresentation, so it is unset rather than guessed. Empty
price renders POA. The Corolla has NO trim badge fitted - do not write "Ascent". To publish: fill
year + price, hit Publish.

## Carsales card anatomy (the stated design reference)

  [21 photo count] / "2015 Toyota Corolla" / "Ascent Auto F" / $17,990 /
  "Drive away ... Excl. Govt. Charges" / FAIR PRICE /
  Sedan . Automatic . 4cyl 1.8L Petrol . 93,083 km / "Dealer used . QLD"

Gaps in car-card.php: no photo-count badge; engine renders as separate pills instead of
"4cyl 1.8L Petrol"; no price qualifier; seller line is "Suburb, STATE" not "Dealer used . VIC";
variant folded into the title.

## Blocked

- Rop still cannot sign in as ropkiplagat - the WP notification email never arrived. Set a password
  via Users -> ropkiplagat -> Edit. The contractor accounts cannot safely be removed until then.
