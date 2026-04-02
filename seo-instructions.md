# Imani Cars — SEO & Google Search Console Setup Guide

## PHASE 1: IMMEDIATELY AFTER GOING LIVE

### Step 1: Upload SEO files to server root
Upload these files to your WordPress root directory (same folder as wp-config.php):
- `sitemap.xml` → https://imanicars.com/sitemap.xml
- `robots.txt`  → https://imanicars.com/robots.txt

Verify both URLs load correctly in your browser before proceeding.

---

### Step 2: Add Imani Cars to Google Search Console

1. Go to https://search.google.com/search-console/
2. Click **"Add property"**
3. Choose **"URL prefix"** and enter: `https://imanicars.com`
4. Click **Continue**
5. Choose **"HTML file"** verification method
6. Download the HTML file (e.g. `google1234abcd.html`)
7. Upload it to your WordPress root via FTP/cPanel
8. Click **"Verify"** in GSC

---

### Step 3: Submit your sitemap to Google

1. In GSC, click **"Sitemaps"** in the left menu
2. Enter `sitemap.xml` in the text field
3. Click **"Submit"**
4. Status should show "Success" within a few minutes

---

### Step 4: Request indexing — do this in PRIORITY ORDER

URL Inspection tool → enter each URL → click **"Request Indexing"**:

**Week 1 — Dealer acquisition pages first:**
1. `https://imanicars.com/list-your-car/` ← MOST IMPORTANT (leads dealers to sign up)
2. `https://imanicars.com/pricing/`
3. `https://imanicars.com/`

**Week 1 — City hubs (drives buyer traffic):**
4. `https://imanicars.com/brisbane/`
5. `https://imanicars.com/melbourne/`
6. `https://imanicars.com/perth/`
7. `https://imanicars.com/darwin/`

**Week 2 — Used cars and dealer directories:**
8. `https://imanicars.com/used-cars/brisbane/`
9. `https://imanicars.com/used-cars/melbourne/`
10. `https://imanicars.com/used-cars/perth/`
11. `https://imanicars.com/used-cars/darwin/`
12. `https://imanicars.com/dealers/`

---

## PHASE 2: FIRST 30 DAYS — CONTENT & LINKS

### Content to publish (in order of SEO priority):

**Blog posts — publish one per week:**
1. `/blog/free-car-listing-platform-dealers-australia/` — Title: "The Free Alternative to carsales for Small Dealers"
2. `/blog/carsales-alternative-small-dealers/` — Title: "How Much Does carsales Really Cost? (And a Cheaper Alternative)"
3. `/blog/used-cars-brisbane-under-20000/` — Target: "used cars Brisbane under $20,000"
4. `/blog/used-suv-brisbane-2025/` — Target: "used SUV Brisbane 2025" (SUVs = 60% of market)
5. `/blog/used-utes-brisbane-ford-ranger-hilux/` — Target: "used ute Brisbane Ford Ranger HiLux"
6. `/blog/used-4wd-darwin-nt/` — Target: "used 4WD Darwin NT"
7. `/blog/cheap-used-cars-perth-under-15000/` — Target: "cheap used cars Perth"
8. `/blog/best-family-cars-melbourne/` — Target: "best family cars Melbourne"
9. `/blog/used-hybrid-cars-australia/` — Target: "used hybrid cars Australia 2025" (PHEV up 130.9%)
10. `/blog/chinese-cars-for-sale-australia-byd-chery/` — Target: "BYD for sale Australia"

### Backlinks to pursue (dealer acquisition AND SEO):

**Free directories — submit immediately:**
- Google Business Profile: https://business.google.com → Add business
- Bing Places: https://www.bingplaces.com
- Apple Maps: https://mapsconnect.apple.com
- Yellow Pages Australia: https://www.yellowpages.com.au
- True Local: https://www.truelocal.com.au
- Hotfrog Australia: https://www.hotfrog.com.au

**Automotive directories:**
- OnlineCars.com.au — submit dealer directory
- CarSurvey.org — register as data partner
- FCAI (Federal Chamber of Automotive Industries) — news mentions

**Dealer outreach for natural backlinks:**
When dealers join Imani Cars, ask them to add a link to their listing:
"Find us on Imani Cars: [dealer URL]"
This creates natural, relevant backlinks from dealer websites.

---

## PHASE 3: ONGOING — WEEKLY MONITORING

### GSC metrics to check every Monday:

**Performance report:**
- Total clicks and impressions week-over-week
- Average position for key terms (target under 10 within 3 months)
- Click-through rate (CTR) — aim for 3%+ for branded, 1%+ for non-branded

**Key terms to monitor in GSC:**
| Keyword | Target position | Timeline |
|---------|----------------|----------|
| cars for sale Brisbane | < 20 | Month 1 |
| used cars Brisbane | < 15 | Month 2 |
| car dealers Brisbane | < 10 | Month 3 |
| list cars free Australia | < 10 | Month 2 |
| carsales alternative | < 20 | Month 3 |
| free dealer listing Australia | < 10 | Month 2 |

**Coverage report:**
- Indexed pages vs excluded pages
- Fix any "Crawl anomaly" or "Not found (404)" errors weekly
- Ensure all 4 city pages are indexed

**Core Web Vitals:**
- LCP (Largest Contentful Paint): target < 2.5s
- CLS (Cumulative Layout Shift): target < 0.1
- INP (Interaction to Next Paint): target < 200ms

---

## KEYWORD TARGETING SUMMARY

### Buyer keywords (drives organic traffic → makes platform valuable to dealers):
- `cars for sale Brisbane / Melbourne / Perth / Darwin`
- `used cars Brisbane / Melbourne / Perth / Darwin`
- `car dealers Brisbane / Melbourne / Perth / Darwin`
- `used Toyota Brisbane / used Ford Ranger Brisbane / used SUV Melbourne`

### Dealer acquisition keywords (B2B — drives free sign-ups):
- `list cars for sale free Australia`
- `free dealer listing platform Australia`
- `carsales alternative for dealers`
- `free alternative to carsales`
- `car marketplace no monthly fee`
- `independent car dealer listing Australia`

### Competitor gap keywords (low competition, high intent):
- `free car listing platform vs carsales Australia`
- `sell car without carsales fees`
- `car listing with view statistics Australia`
- `14-day car listing fresh listings Australia`

---

## SCHEMA IMPLEMENTED

The following JSON-LD schema is injected by the theme into every page:

| Page | Schema Types |
|------|-------------|
| Homepage | Organization + AutoDealer + WebSite + SearchAction |
| City hubs (Brisbane etc.) | AutoDealer with address |
| List Your Car / Pricing | Product with 4 Offer variants |
| Vehicle listings archive | ItemList |
| Single vehicle | Vehicle + Offer |
| All inner pages | BreadcrumbList |
| FAQ sections | FAQPage |

---

## QUICK WIN CHECKLIST (do on day one)

- [ ] Upload sitemap.xml to server root
- [ ] Upload robots.txt to server root
- [ ] Add property in Google Search Console
- [ ] Submit sitemap.xml in GSC
- [ ] Request indexing for /list-your-car/ first
- [ ] Add Imani Cars to Google Business Profile
- [ ] Share /list-your-car/ URL with 10 dealers you know personally
- [ ] Post about Imani Cars in Facebook groups: "Brisbane Car Dealers", "Sell My Car QLD"
- [ ] Write first blog post: "The Free Alternative to carsales for Small Dealers"

---

*Generated by Imani Cars SEO Ranker — 2026*
