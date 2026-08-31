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

## PUBLISHED 2026-08-31 - seven cars live

All seven are status=publish and verified on the public site (HTTP 200 each, zero Unsplash refs,
thumbnail counts match gallery sizes, no stock-photo notice anywhere):

  93747 Mazda 323        1 photo   front 3/4
  93748 Toyota Corolla   1 photo   REAR - still the only outstanding hero problem
  93759 Mazda 6         10 photos  front 3/4, all auction marks removed
  93765 Subaru Forester  5 photos  front 3/4, Pickles + rival dealer sticker removed
  93771 Toyota RAV4      2 photos  front 3/4, spyne.ai studio, year 2018
  93780 Hyundai Sonata   3 photos  front 3/4, no masking needed
  93781 Toyota Yaris     1 photo   front 3/4, no masking needed

Terms created across the session: makes Mazda 497, Toyota 495, Subaru 505, Hyundai 499;
bodies Sedan 522, SUV 524, Hatch 525; city Melbourne 518; condition Used 523.

The two accidental duplicate drafts 93745/93746 are trashed (recoverable from wp-admin Trash).
No drafts remain. Site total: 19 published vehicles = 12 original demo + these 7.

Price renders POA on every card because _ic_price is empty by choice. Odometer is absent by Rop's
instruction. Year is set only on the RAV4, where the filename asserted it.

## Four more cars from WhatsApp (2026-08-31) - 11 real cars live

Rop downloaded WhatsApp media to Downloads himself; that is the working route, not trawling chats.
Both WhatsApp sources reachable from here are empty of cars (107 images in Downloads, and the
"whatsapp photos" album which is 12 videos from 2020).

  93794 Toyota Yaris Hybrid       12 photos  silver, Japanese import
  93808 Honda Vezel               13 photos  black, Japanese import
  93820 Toyota Land Cruiser Sahara 9 photos  black 200 Series V8
  93821 Toyota Prado               2 photos  black
All hero shots are front 3/4. Published and verified live: 200, zero Unsplash refs, thumbnail
counts 12/13/9/2.

Excluded on purpose - do NOT add back:
  - Yaris img 02: a stamped CHASSIS NUMBER on the sill. A VIN on a public page enables history
    lookups on a real vehicle.
  - Yaris img 03: a photo of the key.
  - Vezel img 03: byte-identical duplicate of img 02.

Only ONE plate needed masking across all four (the black Prado's front plate). Japanese auction
photos arrive with plates removed or pre-blurred. The Vezel cluster legibly reads 058013 km; left
visible, because masking an odometer is itself a red flag in car sales.

STILL TO ADD: a GREY Prado, distinct from the black one. Files staged at
C:/Users/cc/imani-cars-photos/prado/pr-03.jpg (clean front 3/4) and pr-04.jpg (same car,
letterboxed phone screenshot - prefer pr-03).

Uploader gotcha: the WP plupload queue silently drops files on batches above ~11 and stalls
part-way. Call window.uploader.start() again and re-check the REST media count until it matches.

## THE WORKFLOW: "Imani Stock" Google Photos album (2026-09-01)

https://photos.google.com/album/AF1QipOQ2g_FH_gnwRKrbqk188nH0KVk5ePcvoazhFO8

Rop drops cars in; an agent takes the whole album end to end - mask plates, strip EXIF, body-type,
publish. CHECK THIS ALBUM FIRST for any "add more cars" request.

Why it exists: the Google Photos "car" search is NOT a stock folder. It runs back to June 2022 and
mixes real cars with carsales.com.au app screenshots of OTHER sellers' cars (recognisable by the
orange Call/SMS/Message bar), Pickles auction invoices, tractors and farm equipment, WhatsApp
contact lists and bank transfers. A blind bulk upload puts a tractor and a bidding record on the
portal. The album is the signal of intent that the search cannot give.

## ROP IS NOW LMCT LICENSED (2026-09-01)

Licensed Motor Car Trader - he can legally sell OTHER PEOPLE'S cars through the portal. This
retires the "whose car is it" blocker that stalled earlier sessions: for listing purposes it no
longer matters, provided the REGO IS MASKED. It does NOT retire: masking, EXIF stripping, or
excluding other dealers' copyrighted listing photography.

**Raised with Rop, unresolved:** the auction invoices in his library include "PURCHASED FROM A
DAMAGED MOTOR VEHICLE SALE - SOLD UNREGISTERED", "Statutory Write-off, unable to be re-registered"
and "HAIL DAMAGE". Under LMCT a statutory write-off is a disclosure he is legally on the hook for.
Photos cannot be matched to invoices from here - Rop must confirm none of the published cars came
from those lots.

## PORTAL POSITIONING: it is a capability demo

Shown to friends and dealerships to prove the platform handles every body type. So coverage across
body types matters more than stock accuracy. Body types now: SUV, Sedan, Hatch, Ute, Wagon, Coupe,
Convertible, People Mover, Van, Caravan (10 tiles). FILLED: SUV, Sedan, Hatch, Ute. EMPTY: Wagon,
Coupe, Convertible, People Mover, Van, Caravan.

15 vehicles published. Utes added 2026-09-01: 93848 Ford Ute, 93849 + 93850 Toyota HiLux SR5.

## Full cleanup pass (2026-08-31)

Published pages 28 -> 18. Everything remaining is legitimate:
  12 home-page (THE FRONT PAGE - page_on_front=12, never unpublish)
  shop / my-account / cart / checkout   kept: WooCommerce is ACTIVE and needs them
  finance sell-your-car contact terms brisbane melbourne perth darwin
  list-your-car pricing dealers used-cars about-imani-car-sales

Drafted (reversible) on top of the 8 demo pages: contacts, faq, blog-2, cart-2, checkout-2,
inventory, loginregister, about-us, contact-us, thank-you, listings. Duplicates and old-theme
leftovers; cart-2/checkout-2/contacts/contact-us were straight duplicates of live pages.

/about/ was a SOFT-404 - no page, no template, but WordPress served an empty document with status
200. Worse than a 404: a link crawl scores it as working and search engines index a blank page.
New page 93843 at /about-imani-car-sales/ with factual content only (Melbourne, used vehicles,
Japanese imports, where to enquire - no invented years/staff/volumes). WordPress REFUSES to release
the "about" slug; nothing queryable in any post type or status holds it, so an old plugin rewrite
likely does. Footer repointed to the new slug (7271c7c).

## BUG FOUND + FIXED: every generic page had an empty <title>

Eight pages rendered "<title> | Imani Cars" with nothing before the separator, including ALL FOUR
city landing pages that exist to rank.

Cause: pre_get_document_title hands its filter an EMPTY STRING. It replaces WordPress's title
generation rather than decorating it, so the old fallback `return $title . ' | Imani Cars'` could
only ever emit the separator. Any page not named explicitly in ic_document_title was affected.
Fixed 73aea9d by building the title (is_singular / is_search / is_404 / archive / site name).

Then 0f1a75a: the first fix prefixed "Used Cars for Sale " onto titles that already read "Cars for
Sale Brisbane", giving "Used Cars for Sale Cars for Sale Brisbane". Only "Used " was missing.
Verified: /brisbane/ now "Used Cars for Sale Brisbane | Imani Cars".

Adding one About page is what exposed a bug affecting eight. Check the rendered <title> of a
generic page after any change to ic_document_title.

## Demo-import leftovers purged (2026-08-31)

The Caleader theme demo import left NINE pages carrying the vendor's demo domain
(smartdata.tonytemplates.com/caleader), EIGHT of them PUBLISHED and publicly reachable. They were
orphaned - none appeared in a link crawl - but they were indexable, and "listing-elements" alone
held 96 links to the theme vendor's site.

Unpublished (draft, reversible): 93413 comparing, 2228 listing-elements, 1953 comparing-2,
1755 testimonial, 1685 services, 1651 about-us-2, 1586 home, 1047 blog-posts.

Checked page_on_front FIRST - it is page 12, so the page slugged "home" (1586) was safely an
orphan. Do not unpublish 12.

Page 3 privacy-policy: the demo domain in its body was replaced with https://imanicars.com. Still a
DRAFT and still linked from the footer, so /privacy-policy/ is the one remaining 404. The rest of
its text is generic WordPress boilerplate about handling customer data - Rop writes that, not an
agent.

Verified: zero PUBLISHED content matches tonytemplates / caleader / smartdata. 31 links crawled,
only /privacy-policy/ broken.

STILL PUBLISHED, likely more junk (not touched - WooCommerce is active and may need some):
shop, cart, cart-2, checkout, checkout-2, my-account, loginregister, inventory, blog-2, listings,
thank-you, faq, home-page, and THREE contact duplicates (contact, contacts, contact-us) plus
about-us. Worth a pass, but check what Woo depends on before unpublishing.

## Link audit + broken-image fix (2026-08-31)

Crawled every internal link. 7 of 36 were 404. Now 30 of 31 resolve.

  /dealers/          MAIN NAV LINK, 404 - page-dealers.php existed, the PAGE did not. Created 93822.
  /used-cars/        same story. Created 93823; 302s to /cars/.
  /terms-of-service/ footer link, but the page has always lived at /terms/. Link repointed.
  /careers/ /advertise/ /news/  no page, no template. Links REMOVED from footer rather than
                     shipping three stub pages. Restoring any = revert fa242ec + create a page.
  /privacy-policy/   STILL 404 BY CHOICE. Page id 3 exists as a draft, but its body is theme
                     boilerplate opening "Our website address is:
                     https://smartdata.tonytemplates.com/caleader". Do not publish as-is - that
                     puts another company's domain in Imani's privacy policy. Rop to write it.

Vehicles: the 12 wrong-image demo listings are now DRAFT. 12 real cars published, including
93825 Toyota Prado TX (grey, 1 photo) - a different vehicle from the black 93821 Toyota Prado.

## FIXED: 24 of 33 Unsplash IDs were dead (2026-08-31)

The theme hardcoded 33 Unsplash photo IDs; 24 now return 404. The homepage "Browse by Body Type"
tiles, the dealer CTA banner, the 404 page, the dealers page and list-your-car were all rendering
alt text instead of photos. A dead <img> fails silently - nothing logged, nothing warned.

Fix (191169c): ic_unsplash() keeps its signature so all 16 call sites work unchanged, but now
returns a self-contained SVG data URI via ic_placeholder_svg(). No remote dependency left to rot.
Body-type tiles go further and use ic_body_type_image(), which pulls the featured image of a real
published vehicle of that type - SUV now shows the Prado, Sedan the Sonata, Hatch the Yaris Hybrid.

SECOND BUG, IN THE FIRST FIX: the placeholder was returned as an SVG data: URI, but all 16 call
sites wrap it in esc_url(), which strips the data protocol unless explicitly allowed. The six
body-type tiles with no car behind them rendered src="" - blank rather than broken, no better.
Fixed in 480c802 by shipping assets/images/placeholder-car.svg as a real file. Verified: 9 tiles =
3 real uploads + 6 placeholder + 0 empty. Lesson: check the rendered HTML of ALL cases, not the
ones that happen to have data behind them.

VERIFY THROUGH THE CACHE: origin (?cb=) shows 0 Unsplash refs, but the plain URL still returned
X-Proxy-Cache: HIT with 39 stale refs. SiteGround's Speed Optimizer plugin is INSTALLED BUT
INACTIVE, so there is no in-WP purge; purge from SiteGround Site Tools or wait for TTL.

Also noted: 11 plugins are dead ("Plugin file does not exist") - the caleader-* and motors-*
suites from a previous car-dealership theme.

## AUDITED: all 12 demo listings show the WRONG CAR (2026-08-31)

Downloaded every featured image and looked at the pixels. The FILENAMES match the listing titles,
which is why this passes a casual check - the images do not. Not one of the twelve is correct:

  93716 2022 Ford Ranger XLT      -> a man with a work light, NO VEHICLE IN FRAME
  93718 2021 Mazda CX-5 Akera     -> Bugatti Chiron
  93726 2022 Nissan Navara Pro-4X -> Scania semi-truck
  93730 2023 Toyota HiLux SR5     -> Ford Mustang
  93728 2019 VW Golf GTI          -> Porsche Panamera
  93732 2021 Mercedes C200 Sedan  -> Mercedes-AMG GT R supercar
  93734 2022 Subaru Forester      -> Audi RS6
  93724 2023 Kia Sportage GT-Line -> Nissan Juke
  93736 2021 Mazda 3 Astina       -> VW Polo
  93714 2022 Toyota RAV4 (SUV)    -> Toyota Camry sedan
  93720 2023 Hyundai Tucson (SUV) -> Hyundai sedan
  93722 2020 BMW 330i             -> BMW M5

A dealer spots a Bugatti badged as a CX-5 immediately. Unpublishing them was refused by the
permission classifier; to do it by hand: Vehicles -> filter Published -> select these twelve ->
Bulk actions -> Edit -> Status: Draft. That leaves the seven real cars as the whole shopfront.

Do NOT assume a matching filename means a correct image. That is exactly the trap here.

## Old note (superseded): STILL WRONG - the 12 original demo listings

They were published before this session and carry stock images of the wrong subject entirely - the
"2022 Ford Ranger" is a workshop close-up with no vehicle in frame. They outnumber the seven real
cars on the archive page. Either replace their featured images or unpublish them; they undercut a
dealership demo far more than any auction sticker did.

## Old note (superseded): READY BUT NOT UPLOADED

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
