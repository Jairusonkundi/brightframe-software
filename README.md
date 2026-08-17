# Brightframe Software — website

A PHP + MySQL rebuild of the original single-file HTML site, structured for
XAMPP: DB-driven services, a working contact form (with server-side
validation, honeypot spam protection, and email notification), and a basic
admin view of incoming leads.

## Project structure

```
BrightFrame_Software/
├── index.php               # Homepage (services + quote form pulled from MySQL)
├── privacy-policy.php      # Placeholder legal page ("Coming soon"), linked from the footer
├── terms.php               # Placeholder legal page ("Coming soon"), linked from the footer
├── css/styles.css          # All styles (extracted from the original inline <style>)
├── js/main.js              # Nav, scroll-reveal, quote-category filter, quote form AJAX submit
├── includes/
│   ├── header.php          # <head>, meta tags, opens <body>, includes topbar.php + nav.php
│   ├── topbar.php           # Thin utility bar: location/hours, phone/email, social icons
│   ├── nav.php              # Two-tier main nav: logo, dropdowns, hamburger, CTA
│   ├── footer.php           # 4-column dark footer + bottom bar, closes </body></html>
│   └── icons.php             # Maps a service's icon_name (category slug) to inline SVG
├── config/
│   ├── db.php               # PDO connection (reads config/.env)
│   ├── env.php               # Tiny .env parser (no Composer dependency)
│   ├── .env.example          # Template — copy to .env and fill in your values
│   └── .env                  # Your local credentials — gitignored, not committed
├── handlers/
│   └── quote_handler.php    # Validates + saves + emails quote requests (was contact_handler.php)
├── admin/
│   └── submissions.php      # Lists quote_requests — UNPROTECTED, see below
├── assets/
│   ├── favicon.svg
│   ├── og-image.svg
│   └── founder-placeholder.svg   # Headshot placeholder — swap out when a real photo exists
├── database.sql             # Full schema + seed data: service_categories, services,
│                             #   quote_requests, quote_request_services
├── sitemap.xml
├── robots.txt
└── .gitignore
```

## 1. Place the project in XAMPP's htdocs

This folder is already named for and intended to live directly under XAMPP's
`htdocs`, e.g.:

```
C:\xampp\htdocs\BrightFrame_Software\
```

If you're starting from a copy elsewhere, just move/copy the whole folder
into `C:\xampp\htdocs\`.

## 2. Import the database

1. Start MySQL in the XAMPP Control Panel.
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
3. Click **Import**, choose `database.sql` from this project, and run it.
   This creates the `brightframe_db` database along with the
   `service_categories`, `services`, `quote_requests`, and
   `quote_request_services` tables, and seeds the 8 categories / 38
   services catalog.

Alternatively, from the command line:

```
"C:\xampp\mysql\bin\mysql.exe" -u root -p < database.sql
```

## 3. Configure `config/db.php`

`db.php` never contains real credentials — it reads them from
`config/.env`, which is gitignored.

1. Copy `config/.env.example` to `config/.env`.
2. Edit `config/.env` with your local MySQL credentials:

   ```
   DB_HOST=localhost
   DB_NAME=brightframe_db
   DB_USER=root
   DB_PASS=
   DB_PORT=3306
   ADMIN_EMAIL=hello@brightframesoftware.com
   MAIL_FROM=no-reply@brightframesoftware.local
   ```

   A stock XAMPP install uses the default MySQL port `3306` with user
   `root` and an empty password — the values above already match that. If
   your MySQL instance runs on a non-default port (check
   `C:\xampp\mysql\bin\my.ini` under `[mysqld]` → `port=`), set `DB_PORT`
   to match — this is common when another MySQL install (e.g. Laragon) is
   also on the machine and XAMPP has been reconfigured to avoid the
   conflict.

## 4. Start Apache and MySQL, then view the site

1. Open the XAMPP Control Panel and click **Start** next to both **Apache**
   and **MySQL**.
2. Visit: `http://localhost/BrightFrame_Software/`
3. Admin quote requests view: `http://localhost/BrightFrame_Software/admin/submissions.php`

## Quote request form notes

- The old generic "contact form" is now a structured "Request a quote"
  form: service category (filter) → specific service(s) as checkboxes,
  grouped by category → project details → budget/timeline (optional) →
  name/email/phone. Submits via `fetch()` to `handlers/quote_handler.php`
  and updates the page inline — no reload, no third-party form service.
- Server-side validation: name, email, phone, project details, and at
  least one selected service are required. Email must pass
  `FILTER_VALIDATE_EMAIL`; phone is checked against a permissive pattern
  (digits/spaces/`+`/`-`/parens, 6–25 chars) rather than a strict format,
  since real-world phone formats vary a lot. Budget/timeline, when
  provided, are validated against the same fixed option lists shown in
  the `<select>`s (a tampered/arbitrary value is rejected).
- Selected services are validated server-side against the real `services`
  table (a tampered/nonexistent id is silently dropped, not trusted) and
  stored in the `quote_request_services` junction table, one row per
  selected service, alongside a snapshot of that service's title.
- Spam protection: a hidden honeypot field (`website`). Real visitors never
  see it; if it's filled in, the request is silently accepted (so bots don't
  learn to avoid it) but nothing is saved or emailed.
- Every valid request is saved to `quote_requests` (+ its services to
  `quote_request_services`) regardless of whether email delivery succeeds
  — the two inserts happen inside a transaction, so a request is never
  half-saved.
- The category `<select>` above the checkboxes is a client-side filter
  only (via `js/main.js`) — without JS, every category's checkboxes stay
  visible (just a longer form), so the form is fully usable without
  JavaScript.
- **Email delivery**: the handler uses PHP's `mail()` function. A stock
  XAMPP install on Windows has **no configured mail server**, so `mail()`
  will typically return `false`/fail locally — this is expected and is
  logged, not fatal. The request is still saved to the database either
  way. To get real local email delivery you have two options:
  - Point `php.ini`'s `[mail function]` section at an SMTP relay (e.g.
    Mercury Mail, bundled with XAMPP, or a real SMTP account), or
  - Swap `mail()` in `handlers/quote_handler.php` for
    [PHPMailer](https://github.com/PHPMailer/PHPMailer) configured with
    SMTP credentials — recommended for production.

## Admin view — no authentication yet

`admin/submissions.php` lists every row in `quote_requests` (with its
selected services shown as tags, pulled from `quote_request_services`)
and has no login and no access control. It's fine for local development,
but **do not deploy it publicly as-is**. Before going live, add one of:

- HTTP Basic Auth via `.htaccess` / `.htpasswd` on the `/admin/` folder, or
- A proper login/session gate, or
- An IP allowlist at the web server level.

The page is also excluded from search indexing (`robots.txt` and a
`noindex` meta tag) but that is not a security control by itself.

## Services are database-driven (2-level: categories → services)

Services are organized in two tables:

- `service_categories` — `id`, `name`, `slug`, `display_order`. Currently 8
  categories (Web Development & Design, Mobile Development, Software &
  Systems Development, ERP & Business Systems, Branding, Digital
  Marketing, SEO, IT & Technical Consulting).
- `services` — `id`, `category_id` (FK → `service_categories.id`, `ON
  DELETE CASCADE`), `title`, `description`, `icon_name`, `display_order`.
  Currently 38 services across the 8 categories. `icon_name` is set to the
  owning category's slug — all services in a category share one icon,
  defined once in `includes/icons.php`.

Three places read from these tables, all driven by the same data:

1. **Services dropdown in the main nav** (`includes/nav.php`) — a 4-column
   mega menu, one column per ~2 categories, each with a heading and its
   service links underneath.
2. **Homepage Services section** (`index.php`) — a quick-nav pill row (jump
   links to each category) followed by one collapsible `<details>` block
   per category, each containing a compact card grid (icon + title +
   one-line description) for that category's services.
3. **Contact form's "Service needed" dropdown** — a single `<select>` with
   one `<optgroup>` per category, native and searchable/typeable in every
   browser without extra JS.

To add, edit, reorder, or recategorize a service, edit these two tables
directly (phpMyAdmin or SQL) — none of the three places above need code
changes. To add a new icon for a new category, add a key to the array in
`includes/icons.php` matching the category's `slug`.

## Header, navigation & footer

The header is two-tier:

- `includes/topbar.php` — a thin utility strip (location, hours, phone,
  email, social icons). Five icons, same set and order in the footer:
  LinkedIn, GitHub, X (all real links — LinkedIn/GitHub/X are Jairus's
  **personal** profiles, not company pages yet; a comment in the file
  marks where to swap in company accounts later), then Facebook and
  Instagram as visible but **non-functional placeholders** — plain
  `<span>` elements (not `<a>`, not `href="#"`), styled at reduced opacity
  so they read as "not yet active" rather than broken. Each has a code
  comment noting there's no account yet and exactly what to change
  (swap the `<span>` for an `<a href="...">`) once one exists.
- `includes/nav.php` — logo, a hamburger toggle (mobile), Services/
  Products/Company dropdowns, a plain "Contact Us" link, and a single
  "Request a quote" button as the only CTA (an earlier "Schedule a call"
  button was removed — it pointed at the same `#contact` anchor as
  "Contact Us" and "Request a quote," so it was pure duplication rather
  than a distinct action). "Contact Us" and "Request a quote" both scroll
  to the same section on purpose: browsing vs. ready-to-act are different
  intents worth supporting differently (a low-key text link vs. a
  prominent button), even though they land in the same place.
- The Company dropdown now holds exactly three items: About Us, Our Team,
  Why Choose Us. "Careers" and a nested "Contact Us" were removed —
  Contact Us lives only as its own top-level nav item now, not duplicated
  inside the dropdown.
- "Blog" was removed entirely (no blog content exists). The Products
  dropdown still intentionally has no real items — just a "coming soon"
  note, per the standing rule against inventing product names.
- **Footer** (`includes/footer.php`) was rebuilt as a 4-column dark panel
  matching the hero/contact-panel navy styling: brand + tagline + the same
  5-icon social set as the topbar; a Company column (About Us, Our Team,
  Why Choose Us, Contact Us); a Services column listing all 8 categories
  (DB-driven — add a category and it appears here automatically); and a
  Contact column (email, WhatsApp, location). A bottom bar holds the
  copyright line and "Privacy Policy" / "Terms" links. "Privacy Policy"
  now goes to a **real, complete policy** (content provided directly by
  the site owner, not drafted or invented here — see "Privacy policy
  page" below). "Terms" still goes to `terms.php`, which remains a
  "Coming soon" placeholder — no legal text was fabricated for it.

## Privacy policy page

`privacy-policy.php` has real content (provided by the site owner
verbatim), not a placeholder. It covers: what's collected via the
quote/contact form, how it's used, data storage, cookies, third-party
services, the right to request deletion, policy changes, and contact
info. Styled with the site's existing type system (Space Grotesk
headings in indigo, Inter body text) at a ~680px reading width. The
"Last updated" date is a **hardcoded literal string** ("August 14,
2026" — today, when this content was added), not computed at request
time: a "last updated" date is supposed to mark when the policy text
itself last changed, so it shouldn't silently drift forward every day
the page is viewed. Update that line by hand the next time the policy's
actual content changes.

## Why Choose Us

A new section (`#why-us`, linked from the Company dropdown and the
footer) with 4 short, benefit-focused cards: Founder-led, Full-stack
under one roof, Clear communication, Built to last. Static content (not
database-driven) — it's brand/positioning copy, not a catalog.

## Request a quote — rebuilt as a structured form

The old single-field "what do you need?" contact form is now a proper
quote-request form, in this order: service category (a `<select>` that
filters the checklist below via JS — see "Quote request form notes"
above for the no-JS fallback), specific service(s) as checkboxes grouped
by category, project details, budget range (optional), timeline
(optional), then name/email/phone. Submit button reads "Send request."
Multi-select was deliberately built as grouped checkboxes rather than a
native `<select multiple>` (bad mobile UX, requires ctrl/cmd-click) or a
custom searchable dropdown (meaningfully more JS complexity/risk for a
site with no other custom widgets) — checkboxes are simple, work
natively on touch, and need no JS to function at all.

## Expanded services catalog — re-import required

The services schema changed shape (flat list → 2-level categories), so
`database.sql` now **drops and rebuilds `services` and
`service_categories`** every time it runs, then reseeds all 8 categories
and 38 services. This is safe to re-run:

- `contact_submissions` (your leads) is never touched.
- Old leads keep displaying correctly even though service ids are
  renumbered by the reseed, because `service_needed` stores a
  human-readable text snapshot taken at submission time — the admin view
  never joins back to `services` live.

**You need to re-import `database.sql`** (phpMyAdmin → Import, or `mysql
-u root -p < database.sql`) for this update to take effect. I already did
this against the local XAMPP MySQL in this environment — see "Testing
performed" below.

**On density**: 38 services is a lot for one homepage section. I pushed
back on a flat 30+ card grid and instead built:
- A quick-nav pill row that jumps straight to a category.
- Each category as a collapsible `<details>` block (open by default for
  scannability/no-JS, but a visitor can collapse ones they don't need).
- Compact cards (small icon, title, one-line description) instead of the
  previous larger cards.

Even so, the full section runs to roughly 1,500–1,800px of scroll on
desktop — substantial, if not unreasonable for a genuine 38-item catalog.
**If that still feels like too much on the homepage**, the cleaner move
would be a dedicated `/services.php` page (grouped the same way, linked
from the nav and from a trimmed-down homepage teaser showing just the 8
category names). That's a bigger change — happy to build it, just say so.

## Database schema change — this round (re-import required)

**`contact_submissions` no longer exists.** It's replaced by two tables,
because the form itself changed shape (one service per lead → multiple
services per request, plus new fields):

- **`quote_requests`** — `id`, `name`, `email`, `phone` *(new — wasn't
  collected before)*, `project_details` *(renamed from `message`)*,
  `budget_range` *(new, nullable)*, `timeline` *(new, nullable)*,
  `submitted_at`, `status`. No more `service_needed` / `service_id`
  columns — see below.
- **`quote_request_services`** *(new junction table)* — one row per
  service selected on a request: `id`, `quote_request_id` (FK →
  `quote_requests.id`, `ON DELETE CASCADE` — deleting a request cleans up
  its service links), `service_id` (soft reference to `services.id`, **no
  FK constraint** — same reasoning as everywhere else in this schema:
  renaming or removing a service later must never block or corrupt a
  historical lead), `service_title` (a snapshot of the service's name at
  submission time, so old requests still read correctly even if the
  catalog changes later).

`database.sql` drops `quote_request_services`, `contact_submissions`, and
`quote_requests` (in that order, to respect the FK) and rebuilds
`quote_requests` + `quote_request_services` clean. **This is a clean
break, not a column migration** — the old table's shape doesn't map onto
the new one (one service vs. many), so if you had real leads in
`contact_submissions`, export them first; re-importing `database.sql`
will not carry them forward. `service_categories` and `services` (the
catalog) are unaffected by this change.

**You need to re-import `database.sql`** for this update to take effect.
I already did this against the local XAMPP MySQL in this environment —
confirmed via `SHOW COLUMNS`/`SHOW TABLES` that `contact_submissions` is
gone and `quote_requests` / `quote_request_services` exist with the right
shape, and that the 8-category/38-service catalog was untouched. See
"Testing performed" below for the full verification.

The handler moved too: `handlers/contact_handler.php` →
`handlers/quote_handler.php` (both the file and the fetch URL in
`js/main.js` were updated together). `admin/submissions.php` now queries
`quote_requests` + `quote_request_services` and displays phone, selected
services (as tags), budget, and timeline alongside the fields it already
showed.

## Visual & UX polish pass

No schema changes this round — visual/interaction only, no re-import needed.

- **Hero illustration**: replaced the plain text-only hero with a two-column
  layout (stacks to one column at ≤1024px) and a custom inline SVG —
  abstract dashboard panels connected by dashed lines and nodes, in the
  existing indigo/cyan palette. No stock photography anywhere on the site:
  the brief steered every section (hero, founder, process) toward
  illustration/icons/placeholders rather than photos, so there was nothing
  to source from Unsplash/Pexels in good conscience without either
  fabricating a URL (explicitly ruled out) or shipping a generic "smiling
  people" photo that doesn't relate to the content (also ruled out). The
  result: zero image payload for the hero — an inline SVG has no network
  request, so "optimize/lazy-load this image" doesn't apply, which is
  arguably the best possible optimization.
- **Founder headshot placeholder**: `assets/founder-placeholder.svg` — a
  silhouette + camera icon + "Add headshot photo" label, referenced via a
  real `<img>` (not a CSS trick) with `width`/`height` set to prevent
  layout shift, `loading="lazy"` (it's below the fold), and alt text that
  tells you exactly what to do with it. Swap the file or the `src` when a
  real photo exists.
- **Process section**: added a small relevant icon to each step (search,
  code brackets, link, layers) and a dashed connector line behind the
  numbered badges on desktop, reinforcing the "flow" between steps.
- **Micro-interactions**: the sticky nav now gains a subtle shadow once
  you scroll past the topbar (`nav.is-scrolled`, toggled in `js/main.js`);
  buttons/cards have explicit `:active` press feedback in addition to
  hover/focus; `<summary>` (the services-category toggles) is now included
  in the global focus-visible styling, which previously only covered
  links/buttons/inputs.
- **Scroll-reveal animations**: sections fade + slide up (22px, ~450ms) as
  they enter the viewport, via `IntersectionObserver` in `js/main.js`.
  This is gated behind an `html.reveal-js` class set by a tiny inline
  script in `header.php` — **only** added when JS actually runs *and* the
  visitor hasn't requested reduced motion. Without that class, `.reveal`
  elements have no special CSS at all and render normally, so nothing
  breaks with JS disabled and nothing animates against a visitor's
  `prefers-reduced-motion: reduce` setting.
- **Type scale**: section-level `<h2>` headings (`.sec-head h2`,
  `.mission-text h2`, `.contact-panel h2`) were three different sizes
  (34px/32px/30px) — unified to 34px (26px on mobile) everywhere that
  heading level is used.
- **Color contrast (WCAG AA)**: audited every text/background pairing site-
  wide and fixed the ones that fell short of 4.5:1 — muted nav/dropdown
  text (`#9EA2B8` → `#6B7280`, was 2.5:1), form placeholder + form-note
  text on the dark contact panel (`#6D7290` → `#8A8FB0`, was 3.6:1), and
  the "contacted" status pill in the admin table (`#9A6B00` → `#92400E`,
  was 4.25:1). Everything else measured — including white-on-indigo button
  text at 4.85:1 — already passed.
- **Touch targets**: bumped to 44×44px minimum on mobile wherever the
  layout allows it — the hamburger button, mobile menu links/CTAs, form
  fields (were 1px short at 43px), quick-nav pills. One deliberate
  exception: the topbar's phone/email/social icons sit in a genuinely thin
  utility strip (34–38px tall) that this same round was asked to keep
  collapsed/minimal on mobile — true 44px targets there would mean
  abandoning that thin-bar design entirely. I maximized their tap area via
  padding within that constraint (~28–34px) rather than force a
  contradiction between the two requirements; the same actions (call,
  email, WhatsApp) are also available as full-size 44px+ targets in the
  hero and contact section.
- **Admin empty state**: was already implemented (a previous round added
  it) — polished with an icon and friendlier copy. A "loading" state was
  not added: this page is a plain server-rendered PHP query with no async
  fetch, so there's no loading moment to represent — adding a fake
  spinner would misrepresent how the page actually works.

### A real bug this pass caught: nav overflow at 1024px

Testing at the specific breakpoints you listed (1440/1024/768/480/375)
surfaced a genuine bug that hadn't been caught before: the full desktop
nav (logo + 8 nav items + 2 CTAs) needs roughly 1150px of width to fit
without crowding, but the hamburger breakpoint was set at 800px — so
1024px (and the whole ~800–1150px range, including common laptop
viewports) rendered the full nav cramped into too little space, pushing
"Request a quote" off the right edge and causing horizontal scroll.
**Fixed by moving the hamburger breakpoint from 800px to 1150px.** Verified
after the fix: zero horizontal overflow at every 100px step from 1024px
to 1440px, and zero at the 5 required breakpoints specifically.

## What changed from the original single-file HTML

- Split into PHP partials (`includes/header.php`, `nav.php`, `footer.php`)
  instead of one monolithic file.
- CSS moved out of a `<style>` block into `css/styles.css`.
- Services grid is now backed by MySQL instead of hardcoded markup.
- Contact form now POSTs to a real PHP handler (with validation, a
  honeypot, and DB persistence) instead of `formsubmit.co`.
- Added visible `:focus-visible` states on all links, buttons, and form
  fields; consistent hover transitions; a few responsive breakpoint fixes
  (smaller hero type and tighter section padding under 560px).
- Added meta description, Open Graph/Twitter card tags, a favicon,
  `sitemap.xml`, and `robots.txt`.
- Fixed a handful of mis-encoded characters from the original file (em
  dashes, the middle dot in the process steps, and the footer's copyright
  symbol).

## Testing performed

This build was verified against a running XAMPP instance in this
environment: `database.sql` was imported cleanly (8 seeded services
confirmed via SQL), the homepage was fetched over HTTP and confirmed to
render all 8 services from the database, and the contact handler was
exercised directly — a valid submission was saved to
`contact_submissions` and appeared correctly in the admin view, an invalid
email was rejected with a 422 and a clear error message, and a honeypot-
filled submission was silently dropped without being written to the
database.

**Header/nav upgrade round** — re-verified with a real headless browser
(Playwright/Chromium) against the running site, at 1280px, 768px, and
480px:
- Desktop: two-tier header renders correctly, the Services dropdown opens
  on hover as a compact 2-column panel, logo strip and numbered-circle
  process cards render as designed, no console errors.
- 768px and 480px: nav collapses into a hamburger; opening it and tapping
  "Services" expands it as an in-place accordion (not a separate popover);
  topbar drops location/hours at 768px and drops text labels (icon-only)
  at 480px.
- Contact form: the new "Project type" dropdown was submitted end-to-end
  with a real `service_id`, with `service_id=other`, and with a tampered/
  nonexistent id — all three resolved correctly (matched title, "Something
  else" label, and a graceful `NULL` fallback respectively) and were
  confirmed in the database, then cleaned up.
- One visual bug found and fixed during this pass: "Contact Us" wrapped
  onto two lines in the desktop nav — added `white-space: nowrap` to
  `.nav-link`.

**Services catalog expansion round** — re-ran `database.sql` against the
local XAMPP MySQL; verified via SQL that `service_categories` has exactly
8 rows and `services` has exactly 38, split across categories exactly as
specified (6/4/5/5/4/5/5/4), and that `contact_submissions` was untouched.
Re-verified with Playwright/Chromium:
- Nav mega menu: all 8 categories × 38 services render, 4 columns, fits
  within a 1280×900 viewport with no internal scrolling needed.
- Homepage Services section: quick-nav pills, all 8 collapsible category
  groups, and all 38 compact cards render; clicking a quick-nav pill jumps
  to and force-opens that category.
- Quote form: dumped the live `<select>` DOM and confirmed all 8
  `<optgroup>`s and 38 `<option>`s match the spec exactly, then submitted
  a real quote (`Local SEO`) end-to-end and confirmed it resolved and
  saved correctly against the new schema.
- Mobile (480px): nav accordion and the services section both collapse to
  a single column cleanly.
- One visual bug found and fixed during this pass: categories whose
  service count wasn't a multiple of 4 left a solid gray block filling the
  empty grid cells in the last row (an artifact of the hairline-divider
  technique used elsewhere in the site). Fixed by switching `.svc-cat-grid`
  from a background-peeking-through-gaps trick to per-card `box-shadow`
  borders, which don't render for cells that don't exist.
- Zero console errors across all checks.

**Visual & UX polish round** — re-verified with Playwright/Chromium at
the 5 specific breakpoints requested (1440/1024/768/480/375px):
- Zero horizontal overflow at all 5, and at every 100px step through the
  800–1440px range (this is what caught the nav-overflow bug described
  above, fixed, then re-verified clean).
- Zero console errors at any breakpoint.
- Nav scroll-shadow confirmed programmatically: `box-shadow` alpha ~0.01
  at scroll position 0, ~0.08 with an 8px offset after scrolling — the
  `.is-scrolled` toggle is working, not just present in the CSS.
- Scroll-reveal confirmed programmatically: `html.reveal-js` is present,
  21 `.reveal` elements exist on the page, and elements already in the
  initial viewport were correctly marked `.is-visible` immediately (the
  expected `IntersectionObserver` behavior — it fires for
  already-intersecting elements as soon as `observe()` is called).
- Touch targets measured directly via `getBoundingClientRect` at 375px:
  hamburger 44×44, mobile nav links 327×48, mobile CTAs 327×48–50, quick-
  nav pills 208×49, form inputs 301×45 (was 301×43 — fixed by bumping
  field padding 12px→13px), submit button 301×44. All ≥44×44.
- Re-ran the full contact-form submission end-to-end after all CSS/markup
  changes (a real quote for "Landing page design") to confirm nothing
  about the visual pass broke the underlying functionality — saved and
  resolved correctly, then cleaned up.
- Reviewed the hero, process, founder, and admin-empty-state screenshots
  directly at each breakpoint.

**Nav/footer/quote-form rebuild round** — re-imported `database.sql` and
confirmed via `SHOW TABLES`/`SHOW COLUMNS` that `contact_submissions` is
gone, `quote_requests` has the new `phone`/`budget_range`/`timeline`
columns, `quote_request_services` exists with the expected shape, and the
services catalog (8 categories / 38 services) was untouched. Re-verified
with Playwright/Chromium at all 5 required breakpoints plus a scan across
every 100px step from 375–1440px:
- Zero console errors, zero horizontal page overflow anywhere.
- Confirmed in the rendered HTML: "Blog" and "Schedule a call" are gone;
  the Company dropdown contains exactly About Us / Our Team / Why Choose
  Us (verified via a live DOM count, not just a visual glance); no
  Twitter/Instagram/Facebook markup remains (the only "hits" for those
  words left were unrelated Twitter Card SEO meta tags and my own code
  comment explaining the removal); the quote form renders exactly 38
  service checkboxes.
- End-to-end quote submission tested with multiple services, budget, and
  timeline — including values containing an en dash (–), which exposed a
  pure testing artifact (Git Bash mangles literal Unicode characters
  passed as command-line arguments to `curl` on this Windows setup) that
  I mistook at first for a validation bug. Confirmed with a controlled
  PHP script sending the exact UTF-8 bytes that the *application* was
  never the problem — a real browser submitting the real form always
  sends correct UTF-8, so this was specific to how I was testing, not a
  bug end users would ever hit. Verified via `HEX()` in MySQL that the en
  dash is stored correctly, and that the admin page renders both the
  budget/timeline text and the selected services as tags correctly.
- Also tested and confirmed working: rejection of a request with zero
  services selected, a missing phone number, and a tampered/nonexistent
  service id; the honeypot still silently drops bot submissions without
  writing to the database; `ON DELETE CASCADE` was confirmed by deleting
  a test `quote_requests` row and verifying its `quote_request_services`
  rows disappeared with it.
- **One real bug found and fixed during this pass**: at narrow mobile
  widths, text inside the quote form's service checkboxes and the
  WhatsApp/email/LinkedIn buttons was visually clipped — invisible to a
  document-level `scrollWidth` check because the clipping happened inside
  `.contact-panel`'s `overflow: hidden`, not at the page level. Root
  cause: `form`, `.field`, `.quote-checkbox`, and `.clink` are all flex or
  grid *items* somewhere in the nesting, and flex/grid items default to
  `min-width: auto`, which refuses to shrink below the content's natural
  width and silently breaks text wrapping at narrow viewports — a classic
  and easy-to-miss CSS default. Fixed by adding explicit `min-width: 0`
  at each of those levels; re-verified with a fresh screenshot and a
  script that measures actual rendered element boundaries (not just
  `document.documentElement.scrollWidth`) to catch this class of bug
  directly next time.
- The Company mobile-accordion, quote-category JS filter (tested by
  selecting "Branding" and confirming only its 4 checkboxes remained
  visible), and the rebuilt dark 4-column footer were all screenshotted
  and reviewed directly at 375px and 1440px.

**Social icons + privacy policy round** — no schema change, no re-import
needed. Verified in the rendered HTML that the topbar and footer each
carry exactly 5 icons in the same order (LinkedIn, GitHub, X, Facebook,
Instagram); that the X link points to `x.com/jairus_onkundi`; that
Facebook/Instagram are `<span>` elements with no `href` anywhere (not
`href="#"`); and that the footer's "Privacy Policy" link points to
`privacy-policy.php`. Checked all 3 pages (`index.php`,
`privacy-policy.php`, `terms.php`) at all 5 required breakpoints — zero
console errors, zero horizontal overflow. **One bug found and fixed
during this pass**: the new `.legal-content a` link styling (indigo,
underlined) was written broadly enough to also match the "Back to home"
`<a class="btn btn-primary">` button, overriding its white text to
indigo — indigo text on the button's indigo background made it
unreadable. Fixed by scoping the link styling to `a:not(.btn)` and
confirmed via a computed-style check that the button's text color is
`rgb(255, 255, 255)`. Also caught and fixed a correctness issue in my own
first draft: I'd generated the "Last updated" date with PHP's `date()`
at request time, which would silently advance every day the page loads —
wrong for a field that's supposed to mark when the policy text itself
was last changed. Replaced with a hardcoded date string.
