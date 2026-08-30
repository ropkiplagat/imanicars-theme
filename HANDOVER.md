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

## Blocked

- Google Photos: the Chrome-extension session has no Photos access (Gmail,
  Calendar, Drive only). Route photos via Drive, a local folder, or direct attach.
