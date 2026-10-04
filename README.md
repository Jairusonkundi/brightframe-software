# Brightframe Software — website

A PHP + MySQL rebuild of the original single-file HTML site, structured for
XAMPP: DB-driven services, a working contact form (with server-side
validation, honeypot spam protection, and email notification), and a basic
admin view of incoming leads.

## Project structure

```
BrightFrame_Software/
├── index.php               # Minimal homepage: hero, logo strip, condensed services overview,
│                           #   why-us teaser, and a CTA band pointing to quote.php
├── services.php            # Services directory (all categories/services from MySQL)
├── services/                # Dedicated per-category detail pages — one level deep, see below
│   ├── web-development-design.php
│   ├── mobile-development.php
│   ├── software-systems-development.php
│   ├── erp-business-systems.php
│   ├── branding.php
│   ├── digital-marketing.php
│   ├── seo.php
│   └── it-technical-consulting.php
├── about.php               # Mission/approach (media-row), process, and founder/team
├── why-us.php               # Media-row intro + full "Why Choose Us" cards
├── quote.php                # "Request a quote" structured form — accepts ?category=<slug>
├── contact.php             # Simple general-inquiry form — separate from the quote form
├── reviews.php              # Public testimonials — approved reviews + a submission form
├── privacy-policy.php      # Real privacy policy content
├── terms.php               # Real terms-of-service content
├── css/styles.css          # All styles (extracted from the original inline <style>)
├── js/main.js              # Nav, scroll-reveal, quote-category filter, all forms' AJAX submit
├── includes/
│   ├── header.php          # <head>, meta tags, opens <body>, wraps topbar+nav in .site-header
│   ├── topbar.php           # Thin utility bar: location/hours, phone/email, social icons
│   ├── nav.php              # Two-tier main nav: logo (links home), dropdowns, hamburger, CTA
│   ├── footer.php           # 4-column dark footer + bottom bar, closes </body></html>
│   ├── icons.php             # Category-slug-keyed lookups: icon SVGs, blurbs, hero intros
│   ├── illustrations.php     # Larger inline SVG illustrations for media-row sections
│   ├── category-content.php  # "Why this matters" / differentiators / "Types of X" copy
│   ├── category-page.php     # Shared renderer for services/<slug>.php (see below)
│   ├── site-stats.php        # Homepage stat tiles — renders nothing until admin-populated
│   ├── mailer.php            # Shared PHPMailer+SMTP helper used by the form handlers
│   ├── admin-auth.php         # Session guard — require at the top of any protected admin page
│   ├── admin-layout-header.php # Admin sidebar shell (opens html, nav, badge counts) — see below
│   └── admin-layout-footer.php # Closes the admin shell opened above
├── config/
│   ├── db.php               # PDO connection (reads config/.env)
│   ├── env.php               # Tiny .env parser (no Composer dependency)
│   ├── .env.example          # Template — copy to .env and fill in your values
│   └── .env                  # Your local credentials — gitignored, not committed
├── handlers/
│   ├── quote_handler.php    # Validates + saves + emails quote requests (quote_requests table)
│   ├── contact_handler.php  # Validates + saves + emails contact messages (contact_messages table)
│   └── review_handler.php   # Validates + saves a review as 'pending' (reviews table)
├── admin/                    # See "Admin view" below for what each page does
│   ├── login.php
│   ├── logout.php
│   ├── index.php             # Dashboard (landing page after login)
│   ├── quotes.php / quote-view.php
│   ├── messages.php / message-view.php
│   ├── reviews.php
│   ├── services.php          # CRUD for service_categories + services
│   └── stats.php              # Analytics + the homepage stat-tile editor
├── assets/
│   ├── favicon.svg
│   ├── og-image.svg
│   └── founder-placeholder.svg   # Headshot placeholder — swap out when a real photo exists
├── database.sql             # Full schema + seed data: service_categories, services,
│                             #   quote_requests, quote_request_services, contact_messages,
│                             #   reviews, site_stats, admin_users, login_attempts
├── sitemap.xml
├── robots.txt
├── composer.json            # PHPMailer dependency — run `composer install` (see §5)
├── vendor/                  # Composer packages (gitignored, created by `composer install`)
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
2. Edit `config/.env` with your local MySQL credentials **and** email settings:

   ```
   DB_HOST=localhost
   DB_NAME=brightframe_db
   DB_USER=root
   DB_PASS=
   DB_PORT=3306

   ADMIN_EMAIL=jairusonkundi@gmail.com

   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=465
   SMTP_SECURE=ssl
   SMTP_USER=jairusonkundi@gmail.com
   SMTP_PASS=PASTE_YOUR_GMAIL_APP_PASSWORD_HERE
   MAIL_FROM=jairusonkundi@gmail.com
   MAIL_FROM_NAME=Brightframe Software
   ```

   A stock XAMPP install uses the default MySQL port `3306` with user
   `root` and an empty password — the values above already match that. If
   your MySQL instance runs on a non-default port (check
   `C:\xampp\mysql\bin\my.ini` under `[mysqld]` → `port=`), set `DB_PORT`
   to match — this is common when another MySQL install (e.g. Laragon) is
   also on the machine and XAMPP has been reconfigured to avoid the
   conflict.

   `SMTP_PASS` is the one secret you must supply yourself: Gmail won't
   accept your normal sign-in password via SMTP. Create an **App Password**
   (Google Account → *Security* → *2-Step Verification* → *App passwords*)
   and paste it in — see includes/mailer.php for the full key list. If you
   leave `SMTP_USER`/`SMTP_PASS` empty the mailer falls back to PHP's
   `mail()` so local dev keeps working.

## 4. Install PHP dependencies (email)

Form notifications use PHPMailer, managed by Composer. Once, from the
project root:

```
composer install
```

This creates `vendor/` (gitignored). Composer is the only requirement —
there are no other dependencies. Skip this and the site still works; only
the email notifications need it, and even then the forms still save to the
database if the SMTP settings aren't filled in yet.

## 5. Start Apache and MySQL, then view the site

1. Open the XAMPP Control Panel and click **Start** next to both **Apache**
   and **MySQL**.
2. Visit: `http://localhost/BrightFrame_Software/`
3. Admin panel (requires login — see "Admin login" below for the seeded
   username and how to change the password): `http://localhost/BrightFrame_Software/admin/login.php`

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
- **Email delivery**: notifications are sent via
  [PHPMailer](https://github.com/PHPMailer/PHPMailer) + SMTP (see
  `includes/mailer.php`), driven by the `ADMIN_EMAIL` / `SMTP_*` values in
  `config/.env`. A failed email is logged but never breaks the form — the
  request is already saved to the database by that point. Both the quote
  handler and the contact handler go through the same helper, so changing
  the recipient or relay is a single `.env` edit.

## Admin view — session login required, sidebar dashboard

Every page under `/admin/` (except `login.php` itself, which IS the
login) requires login — `includes/admin-auth.php` (required at the top
of each one, before any output) redirects to `admin/login.php` if
`$_SESSION['admin_user_id']` isn't set. It also generates a per-session
CSRF token (`$_SESSION['admin_csrf']`) and exposes `admin_csrf_check()`,
which every admin POST handler calls before touching the database. See
"Admin login" below for the full auth setup. All admin pages share one
shell (`includes/admin-layout-header.php` / `admin-layout-footer.php`,
mirroring the public site's own header.php/footer.php split): a dark
sidebar with per-section icons, badge counts, and a user card (avatar
initial, username, "Administrator", log out), plus a slim topbar above
the content area (mobile menu toggle, current section name, a "View
site" link out to the public site, and the avatar again) — the site's
navy/indigo/cyan branding and type system throughout, not a generic
scaffold.

- `admin/index.php` — **Dashboard**, the landing page after login. New/
  total counts for quotes and messages, pending review count, and a
  merged recent-activity feed across all three submission types.
- `admin/quotes.php` — quote request list, filterable by status and
  sortable by date. `admin/quote-view.php?id=<n>` — full detail (project
  description, requested services, budget, timeline, contact info) plus
  a status dropdown (New / Contacted / In discussion / Closed).
- `admin/messages.php` — contact message list, same filter/sort pattern.
  `admin/message-view.php?id=<n>` — full detail; viewing a "new" message
  auto-advances it to "read" (standard inbox behavior), with a status
  dropdown (New / Read / Replied) for manual control.
- `admin/reviews.php` — the review moderation queue (Pending / Approved /
  Rejected tabs). Rejecting can optionally record a short internal reason
  (`reviews.reject_reason`) — admin-facing only, never shown publicly.
- `admin/services.php` — CRUD for `service_categories` and `services`,
  so the catalog no longer requires direct database edits. Adding a
  category auto-creates its dedicated `services/<slug>.php` detail page
  too (a 3-line shim through `includes/category-page.php`, which renders
  from the database) — see the comment at the top of that file. Renaming
  an *existing* category's slug still breaks its existing detail page,
  since that file is keyed to the old slug on disk (flagged inline on
  the edit form); `includes/icons.php`'s `category_detail_url()` falls
  back to `services.php#cat-<slug>` if a page is ever missing, so nothing
  404s either way.
- `admin/stats.php` — two distinct things on one page: real analytics
  (counts pulled live from the database, plus a submissions-per-week bar
  chart) at the top, and the admin-editable homepage stat-tile editor
  below it. These are genuinely different — the analytics are automatic
  and admin-only; the tiles are manual and public.

All admin pages are also excluded from search indexing (`robots.txt` and
a `noindex` meta tag), on top of the login requirement.

## Admin login

Credentials live in the `admin_users` table (`username`,
`password_hash` — bcrypt via PHP's `password_hash()`/`password_verify()`,
never plaintext). Seeded with one user on first import — see the comment
above `admin_users` in `database.sql` for the seeded username and how to
change the password later. That table is deliberately **not**
dropped/rebuilt when `database.sql` is re-imported after a future schema
change, unlike every other table in this file — re-seeding it every time
would silently reset the password back to the original value with no
warning.

`admin/login.php` handles the form: CSRF token (session-bound, checked
via `hash_equals()`), a generic "Invalid username or password" error
either way (doesn't confirm which part was wrong), and IP-based rate
limiting — 5 failed attempts from one IP within 15 minutes blocks further
attempts (including a *correct* password) until the window passes; see
the `login_attempts` table. A successful login clears that IP's attempt
history, regenerates the session id (`session_regenerate_id(true)`,
prevents session fixation), and sets the session cookie `httponly` +
`samesite=Lax`. `admin/logout.php` clears the session and its cookie.

`?redirect=<page>` on the login URL sends you back to whatever admin
page you originally requested — validated against a strict
`^[a-zA-Z0-9_-]+\.php$` pattern first, so it can't be turned into an
open redirect to an external URL.

This covers the admin panel specifically. It does not add HTTPS,
firewall rules, or anything at the hosting/infrastructure level — add
those before this goes on a real domain.

## Services are database-driven (2-level: categories → services)

Services are organized in two tables:

- `service_categories` — `id`, `name`, `slug`, `display_order`. Currently 8
  categories (Web Development & Design, Mobile Development, Software &
  Systems Development, ERP & Business Systems, Branding, Digital
  Marketing, SEO, IT & Technical Consulting).
- `services` — `id`, `category_id` (FK → `service_categories.id`, `ON
  DELETE CASCADE`), `title`, `description`, `long_description`,
  `icon_name`, `display_order`. Currently 38 services across the 8
  categories. `icon_name` is set to the owning category's slug — all
  services in a category share one icon, defined once in
  `includes/icons.php`. `description` is the short one-liner used in
  compact contexts (services.php's card grid, the quote form checklist);
  `long_description` is the expanded 2-3 sentence version used only on
  `services/<slug>.php`, which has room for it.

Several places read from these tables, all driven by the same data:

1. **Services dropdown in the main nav** (`includes/nav.php`) — categories
   only (not individual services): an icon, name, and service count per
   category, each linking to its section on `services.php`.
2. **`services.php`** — the full catalog: a sticky category sidebar
   (scroll-spy highlights the section currently in view) alongside
   always-open category sections, each a card grid (icon + title +
   one-line description) for that category's services.
3. **Homepage services overview** (`index.php`) — a condensed 8-card grid,
   one per category (icon, name, one-line blurb from `category_blurb()` in
   `includes/icons.php`), linking out to that category's section on
   `services.php`.
4. **`quote.php`'s service checklist** — checkboxes grouped by category
   (`<fieldset>` per category), filterable by the category `<select>` above
   it.

To add, edit, or delete a category or service, use `admin/services.php`
(see "Admin view" above) rather than editing these tables directly —
none of the four places above need code changes either way, and adding
a category through the admin page also creates its dedicated detail
page automatically. Direct SQL is still fine for bulk changes (reordering
via `display_order`, etc.). To add a new icon (or update the one-line
blurb) for a new category, add a key to the respective array in
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
  Products/Company dropdowns, a plain "Contact Us" link (→ `contact.php`),
  and a single "Request a quote" button as the only CTA (→ `quote.php`).
  These are deliberately two separate pages/forms, not two links to the
  same place: browsing a general question vs. requesting a structured
  quote are different intents worth supporting differently.
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

## Privacy policy & terms pages

Both `privacy-policy.php` and `terms.php` have real content (provided by
the site owner verbatim), not placeholders — `terms.php` was the last one
still saying "Coming soon" and is now real too. Each covers what you'd
expect: privacy covers what's collected via the quote/contact forms, how
it's used, data storage, cookies, third-party services, the right to
request deletion, and policy changes; terms covers site use, that a quote
request isn't a binding contract, IP ownership, liability, third-party
links, and governing law (Kenya). Both styled with the site's existing
type system (Space Grotesk headings in indigo, Inter body text) at a
~680px reading width. Each "Last updated" date is a **hardcoded literal
string** (privacy: "August 14, 2026"; terms: "August 17, 2026" — the
actual date each was added), not computed at request time: a "last
updated" date is supposed to mark when the text itself last changed, so
it shouldn't silently drift forward every day the page is viewed. Update
the relevant line by hand whenever that page's actual content changes.

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

## Header fix, real legal pages, and Contact Us vs. Request a Quote — this round

**Database change**: one new table, `contact_messages` (`id`, `name`,
`email`, `subject`, `message`, `submitted_at`, `status`) — a completely
new table, not a migration of anything existing, so **re-importing
`database.sql` is required but non-destructive**: it only adds
`contact_messages` and re-seeds the catalog tables as always;
`quote_requests` / `quote_request_services` are untouched. The filename
`handlers/contact_handler.php` is back, but as an entirely new file for
this new table — it's unrelated to the old contact_handler.php from
before the quote-form rewrite (that one is long gone; this is a fresh
build with a different table and different fields).

**Header behavior fix**: the topbar and nav previously behaved
inconsistently — only `<nav>` was `position: sticky`, so the topbar
scrolled away while the nav stayed pinned, which read as broken. Both are
now wrapped in one `.site-header` div with the sticky positioning moved
to that wrapper, so they move together as a single unit. Verified
programmatically (not just visually): topbar and nav report the exact
same `getBoundingClientRect().top` values before and after an 800px
scroll.

**Logo/Home now link everywhere, and so does everything else that
should**: the logo + "Brightframe Software" text in the nav are now a
real `<a href="index.php">`, and this surfaced a bigger, pre-existing
bug — every homepage-section link in the shared nav/footer (`#services`,
`#about`, `#team`, `#why-us`, `#quote`, the Services mega-menu, the
footer's Company/Services columns) was a bare `#fragment` href, which
only worked when you were already on `index.php`. Clicking any of them
from `privacy-policy.php` or `terms.php` silently did nothing. Fixed by
prefixing all of them with `index.php#...` so they resolve correctly
from any page (verified: clicking "Request a quote" from `terms.php`
now lands on `index.php#quote`, confirmed via the resulting page URL and
that the section is visible after navigation).

**Logo redesign — proposed as 3 directions, then "Focus Frame" applied.**
Three directions (SVG, using only the existing navy/indigo/cyan palette)
were built and published as a review artifact first — nothing was swapped
into the live site silently. Two of the three initial sketches failed my
own visual review before you ever saw them (one read as a video "play"
button instead of a prism, one read as a messy blob) — both were
redesigned and re-tested before publishing. After reviewing, "Focus
Frame" (four open corner brackets + a center four-point spark, gradient
indigo→cyan) was chosen and is now live in all four places:
- `assets/favicon.svg` — the mark on its navy badge, self-contained.
- `includes/nav.php` — same mark + badge (needs the badge here since the
  brackets are white and the nav bar itself is light).
- `includes/footer.php` — same mark, **badge omitted**: the badge's fill
  is the exact same navy as the footer background, so on that surface
  it's invisible anyway — cleaner to just not draw it there.
- `assets/og-image.svg` — the mark rebuilt at 2x scale (via an SVG
  `scale(2)` transform on the same path data, not a separate redraw) in
  the social-preview card's top-left corner.

Each usage has its own `<linearGradient id="...">` with a unique id
(`logoSparkNav`, `logoSparkFooter`, `logoSparkFavicon`, `logoSparkOg`) —
SVG/HTML ids must be unique per document, and nav.php + footer.php render
on the same page simultaneously, so reusing one id across both would have
been invalid markup.

**Contact Us vs. Request a Quote — now genuinely different forms, not
the same content behind two labels:**

| | Request a Quote | Contact Us |
|---|---|---|
| Location | `index.php#quote` (homepage section) | `contact.php` (own page) |
| Purpose | Ready to describe a specific project | General question, not ready to commit to project details |
| Fields | Category filter, multi-service checkboxes, project details, budget, timeline, name/email/phone | Name, email, subject, message |
| Heading | "Request a quote" | "Have a question? Get in touch" |
| Table | `quote_requests` + `quote_request_services` | `contact_messages` |
| Handler | `handlers/quote_handler.php` | `handlers/contact_handler.php` |

Each page cross-links to the other ("Just have a question instead?" /
"Ready to talk pricing instead?"), so a visitor who lands on the wrong
one isn't stuck. `admin/submissions.php` shows both tables as clearly
separated sections (not tabs — a tab UI would hide one behind a click;
plain sections with a jump-nav at the top keep both visible and
scannable, and don't depend on JS to work).

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

**Header fix, real terms page, and split Contact/Quote round** —
re-imported `database.sql` and confirmed via `SHOW TABLES`/`SHOW COLUMNS`
that `contact_messages` exists with the right shape and that
`quote_requests`/`quote_request_services`/the catalog were untouched.
Re-verified with Playwright/Chromium across `index.php`, `contact.php`,
`terms.php`, and `privacy-policy.php` at all 5 required breakpoints —
zero console errors, zero horizontal overflow on any of the 4 pages.
- Sticky header: measured `getBoundingClientRect()` on both the topbar
  and nav before and after an 800px scroll — identical positions both
  times (`topbarTop: 0, navTop: 38` unchanged), confirming they move as
  one unit rather than the old broken one-sticky-one-not behavior.
- Cross-page navigation: clicked the logo from `privacy-policy.php` and
  confirmed the resulting URL was `index.php`; clicked "Request a quote"
  from `terms.php` and confirmed it landed on `index.php#quote` with the
  section actually visible — both were silently broken before this round
  (bare `#fragment` hrefs only work on the page that has that fragment).
- Both new/changed handlers tested end-to-end: a full contact message
  (validated, saved, honeypot-tested — bot submission correctly produced
  no database row), and a full quote request re-run unchanged to confirm
  the split didn't regress it. `admin/submissions.php` checked directly
  and showed both a quote request and a contact message in their correct,
  separate sections with accurate counts.
- Logo proposal: built 3 SVG concepts, self-tested via a local Playwright
  screenshot pass *before* showing them to the user — 2 of the first 3
  sketches failed that review (one read as a video play button, one as a
  messy overlapping blob) and were redesigned and re-tested. The final
  comparison page was itself screenshotted in both light and dark theme
  and at a 390px mobile width, which caught one real bug — the mock nav
  bar's CTA button overflowed at narrow widths (the same flex
  `min-width: auto` issue documented in the visual-polish round above,
  recurring in new markup) — fixed and re-verified before publishing.

**Sticky-header re-confirmation + Focus Frame applied** — re-verified the
sticky header claim at full page depth rather than a single scroll
position: checked `.site-header`'s `getBoundingClientRect()` at 6 scroll
positions spanning the entire ~8,000px homepage, from the very top to
the very bottom (footer visible). `top` stayed `0` at every checkpoint —
confirmed, not assumed. After applying the Focus Frame mark to all 4
files, re-ran the full check (all 4 pages × all 5 required breakpoints):
zero console errors, zero horizontal overflow. Confirmed no leftover
references to the old checkmark path anywhere in the codebase via a
direct grep for its exact path data.

## Floating back-to-top + WhatsApp widget; "Call" replaces inline WhatsApp

No schema change this round. Two fixed-position buttons, bottom-right,
rendered once in `includes/footer.php` (`.floating-actions`) so they
appear on every page: a back-to-top button (hidden until you scroll past
~500px, `#back-to-top` in `js/main.js`) stacked above an always-visible
WhatsApp widget linking to `wa.me/254743192585`.

Since WhatsApp is now reachable globally via that widget, the inline
"WhatsApp: ..." button inside the two forms' contact-links (on
`index.php#quote` and `contact.php`) was redundant — replaced with a
`tel:` **Call** link instead, so those two spots now offer a genuinely
different contact option rather than duplicating the widget. The CSS
class backing that highlighted button was renamed `.wa-btn` →
`.clink-primary`, since keeping a WhatsApp-branded class name on a phone
button would have been misleading to anyone reading the CSS later.
`includes/footer.php`'s own "Contact" info column still lists WhatsApp
as before — that wasn't in scope, only the two forms were.

Verified: back-to-top is `visibility:hidden` at the top of the page,
becomes visible after scrolling 1200px, and clicking it drives
`window.scrollY` back to `0`. Checked mobile (375px) at the very bottom
of the page to confirm the floating buttons don't cover the footer's
Privacy Policy / Terms links. Full 4-page × 5-breakpoint regression
re-run afterward: zero console errors, zero horizontal overflow.

## Cache-busting for CSS/JS, and a real overflow bug the breakpoint list missed

A user report ("header disappears when I scroll") turned out to be two
things layered together — one browser-side, one a genuine bug:

**Stale browser cache.** `css/styles.css` and `js/main.js` were linked by
plain filename with no version marker, so once a browser cached them, it
had no reason to re-fetch after an edit — visitors could keep seeing
whatever CSS was cached from before any given round's fixes indefinitely.
Fixed by appending `?v=<the file's filemtime()>` to both links (and to
`admin/submissions.php`'s separate stylesheet link, which isn't served
through `includes/header.php`). This updates itself automatically
whenever either file is actually edited — no manual version bumping, and
existing caches invalidate the moment the file changes on disk.

**A real, previously-uncaught overflow bug.** Reproducing the report's
exact scenario (a hard navigation straight to `index.php#quote`, address
bar and all) showed the header rendering correctly — so I widened the
search instead of concluding "just cache." A sweep across every 40px from
360–1920px (this project's earlier breakpoint checks only ever tested
five fixed widths: 1440/1024/768/480/375) with the Services mega menu
actually opened at each one found real horizontal overflow at 1280px
specifically. Cause: `.dropdown-mega` was positioned `absolute`, anchored
to the left edge of the "Services" nav trigger — at 800px wide, whether
that placement fits depends on exactly where "Services" sits in the nav,
which isn't the same at every viewport width. It happened to clear the
five previously-tested widths and fail at 1280px, which nothing had
checked before. Fixed by repositioning the mega menu as `position: fixed`
and centered under the header (`left: 50%` + `transform: translateX(-50%)`,
independent of the trigger's position) rather than anchored to its
trigger — the standard approach for wide mega menus, and one that can't
overflow regardless of where the triggering nav item happens to sit.
Re-verified with the same 40-width × dropdown-open sweep: zero overflow.
This class of bug (something invisible/`visibility:hidden` still
affecting `scrollWidth`) doesn't show up by eyeballing a page — it needs
an actual measurement, which is why the fixed breakpoint list had missed
it across every previous round.

## Multi-page restructuring — every nav destination is now a standalone page

No schema change this round. Two requests drove this: the quote form
shouldn't require scrolling through the whole homepage to reach, and
clicking a nav item like "Contact Us" should open only that page, not
scroll within `index.php`.

**New standalone pages**, each following the same pattern as the
pre-existing `contact.php` (own `$pageTitle`/`$pageDescription`, shared
`includes/header.php`/`includes/footer.php` so the header, nav, footer,
and floating WhatsApp/back-to-top widgets stay identical everywhere):

- `services.php` — the full catalog (all 8 categories, all 38 services),
  moved out of `index.php`'s old `#services` section.
- `about.php` — mission/approach (`#about`), the 4-step process
  (`#process`), and the founder/team card (`#team`) combined into one
  page — these three used to be separate homepage sections.
- `why-us.php` — the full 4-card "Why Choose Us" grid.
- `quote.php` — the structured "Request a quote" form, moved out of
  `index.php`'s old `#quote` section. Field names/ids (`#quote-form`,
  `service_ids[]`, etc.) are unchanged, so `handlers/quote_handler.php`
  and the existing `js/main.js` submit/filter logic needed no changes.

**`index.php` was trimmed to a minimal home**: hero, trust logo strip, a
new condensed services overview (8 category cards linking to
`services.php#cat-x`, not all 38 services), a new short 3-point why-us
teaser (linking to `why-us.php`), and a new CTA band (eyebrow, headline,
buttons to `quote.php`/`contact.php`, reassurance line) replacing the old
inline quote section. New CSS: `.svc-overview-grid`/`.svc-overview-card`,
`.whyus-teaser-grid`/`.whyus-teaser-item`, and `.cta-band` (reuses the
existing navy/radial-glow treatment from `.hero`/`.contact-panel`).

**`includes/nav.php` and `includes/footer.php`** had every
`index.php#anchor` href updated to point at the new pages
(`about.php`, `about.php#team`, `why-us.php`, `services.php#cat-x`,
`quote.php`). The Services mega-menu's category headings are now links
too (previously plain text), which needed a small specificity fix in
`css/styles.css` (`.dropdown-panel a` was otherwise overriding
`.dropdown-col-head`'s color/size once the heading became an `<a>`).

Verified: full 8-page × 8-breakpoint sweep (360–1920px) — zero console
errors, zero horizontal overflow. Submitted both the quote and contact
forms end-to-end from their new standalone pages and confirmed successful
`POST` responses from `handlers/quote_handler.php` and
`handlers/contact_handler.php` (relative paths still resolve correctly
since both pages live in the project root, same as `index.php` did).
Confirmed nav/footer link destinations resolve correctly, the header
logo still links home, the WhatsApp/back-to-top widgets render on
non-home pages, and in-page anchor scrolling (`services.php#cat-x`)
still respects `--header-h` via `scroll-margin-top`.

## Services mega-menu simplified to categories; services.php redesigned

No schema change this round. Two related requests: the nav's Services
dropdown was listing all 38 individual services (dense, slow to scan),
and `services.php` itself needed a more modern layout.

**Mega-menu**: `includes/nav.php` now queries categories with a
`COUNT(s.id)` per category instead of the full category→services join it
used before, and renders one card per category (icon, name, service
count) linking to `services.php#cat-x` — not a per-service link list.
This also shrank the panel from a `min-width: 800px` 4-column grid to a
`min-width: 480px` 2-column one.

**`services.php`**: replaced the quick-nav-pills + collapsible
`<details>`-per-category layout with a sticky category sidebar
(desktop) alongside always-open category sections — since the sidebar
is now the navigation, collapsing sections no longer earned its
complexity. `js/main.js` gained a small `IntersectionObserver` scroll-spy
that highlights the sidebar link for whichever category section is
currently in view (replacing the old JS that force-opened a `<details>`
group before scrolling to it, which is no longer needed since nothing
collapses). On narrow screens the sidebar becomes a static stack of
full-width rows above the content instead of a floating sidebar; each
category section's cards moved from a shared-border flush grid to
individual bordered cards with a hover lift, matching the treatment
already used for the homepage's services overview cards.

Two small pieces of shared category metadata (icon lookup and a
one-line blurb) were already duplicated between `index.php` and this
page's design — consolidated into `render_service_icon()` and a new
`category_blurb()`, both in `includes/icons.php`, so both pages (and any
future one) pull from the same source instead of maintaining separate
copies.

**Dead CSS removed**: `.svc-quicknav`, `.svc-cat`/`.svc-cat-summary`/
`.svc-cat-name`/`.svc-cat-count`/`.svc-cat-grid`, `.svc-mini-card`/
`.svc-mini-ico`, and `.dropdown-col`/`.dropdown-col-head` — all only
existed to support the layouts this round replaced, confirmed via a
project-wide grep before deleting.

Verified: PHP lint on every changed file; a 6-page × 8-breakpoint sweep
(360–1920px) with the Services mega-menu opened at every width (this
project's mega-menu has caused a real overflow bug before, at 1280px
specifically — re-checked explicitly this round) — zero console errors,
zero horizontal overflow at any width, open or closed. Confirmed via
screenshot that the sidebar's sticky positioning and scroll-spy active
state work while scrolling, and that the mobile stacked-sidebar layout
renders cleanly.

## Nav highlights the current page, hover shows an underline

No schema change this round. `includes/nav.php` now computes which
top-level nav item matches the page actually being requested
(`basename($_SERVER['SCRIPT_NAME'])`) and marks it `is-current`: "Home" on
`index.php`, "Services" on `services.php`, "Company" on `about.php` or
`why-us.php` (plus the matching item inside that dropdown — "About Us" /
"Our Team" vs. "Why Choose Us"), "Contact Us" on `contact.php`.

CSS: `.nav-link` gained an animated underline (`::after`, `scaleX(0)` →
`scaleX(1)`) that grows in on hover/focus and stays fully drawn for
`.is-current` — so which page you're on doesn't depend on remembering to
hover. On the mobile stacked menu the underline is disabled (it would
collide with each row's existing `border-bottom` divider) in favor of a
background tint, applied the same way for hover and `.is-current`.

**A real bug this pass caught**: testing the mobile menu surfaced that
the Services dropdown panel was opening automatically the moment the
hamburger was tapped, before ever touching "Services" — a leftover from
last round's mega-menu redesign. `.dropdown-mega`'s mobile rule was
unconditionally declaring `display: flex`, with equal specificity to (and
appearing after, in source order) the correct `.dropdown-panel { display:
none }` default — so it always won, regardless of `.nav-dropdown.is-open`.
Scoped it out. That same investigation found a second instance of the
same shape of bug: `.nav-dropdown.is-open .dropdown-mega`'s desktop
centering transform (`translateX(-50%)`, for centering the fixed-position
panel under the header) has higher specificity than the mobile
`.dropdown-panel { transform: none }` reset, so opening the panel on
mobile shifted it ~171px off-screen to the left instead of sitting in
normal document flow. Fixed with an explicit
`.nav-dropdown.is-open .dropdown-mega { position: static; transform: none; }`
inside the mobile media query.

Verified: PHP lint; re-ran the 6-page × 8-breakpoint sweep with the
mega-menu opened at every width (including the mobile accordion tap this
round's fixes target) — zero console errors, zero overflow. Screenshots
confirm hover/current states render correctly and independently (hovering
"Home" while "Contact Us" is current shows both underlined at once), the
Company dropdown highlights only the matching sub-item, and the mobile
Services accordion now opens/closes only on tap, positioned correctly in
the document flow.

## Dedicated per-category service pages, image+text media rows, quote pre-select

Each of the 8 service categories now has its own detail page at
`services/<slug>.php` — hero (breadcrumb, category illustration, H1,
intro, "Request a quote for this service" CTA), the full list of that
category's specific services as a detail list (not a card grid — title +
full description, room to breathe), and a closing CTA band. Every nav
entry point that used to link to `services.php#cat-x` (homepage cards,
the nav mega-menu, the footer's Services column) now links straight to
the matching dedicated page instead; `services.php` itself keeps its full
card-grid catalog for browsing everything at once, but each of its
category sections gained a "Full details →" link out to the dedicated
page, so the two pages point at each other.

**Routing**: each `services/<slug>.php` is a two-line file
(`$categorySlug = '...'; require '../includes/category-page.php';`) —
plain files rather than a rewrite-based router, so nothing depends on
Apache config the way `mod_rewrite` would. One deviation from the
originally-requested filenames: IT & Technical Consulting is
`services/it-technical-consulting.php`, matching the category's existing
DB slug (already used for its icon key and its `services.php#cat-x`
anchor) rather than the shorter `it-consulting.php` first suggested —
since every nav/footer/homepage link to this page is generated as
`services/<?= $cat['slug'] ?>.php`, a mismatched filename would have
needed a special-case exception everywhere that link is built.

**`$basePath`**: since the new pages live one level below the project
root, `includes/header.php`, `nav.php`, and `footer.php` now read a
`$basePath` variable (default `''`, root pages don't need to set it;
`category-page.php` sets it to `'../'`) and prefix every internal
asset/page link with it. This is the first subdirectory content page in
the project — everything before this lived flat at root — so this was
new plumbing, not an existing pattern to reuse. While in here, also fixed
`<link rel="canonical">`, which had hardcoded the homepage URL on every
page since it was first added — now reflects the actual requested path.

**Illustrations**: 8 new hand-built inline SVGs (one per category —
browser mockup, phone mockup, connected nodes, dashboard grid, color
palette, megaphone, growth chart, network/gear), all sharing one navy
dot-pattern "frame" (matching `index.php`'s existing hero-art) so the set
reads as one family. Self-QA'd via a local test page + Playwright
screenshot before wiring in (per the project's established pattern for
new SVG work) — caught and fixed two disconnected stray shapes in an
early pass. Per the no-fabricated-image-URLs rule, these are all
original SVG, not stock photos.

**Reusable media-row pattern**: `.media-row` / `.media-row-reverse` — a
side-by-side image+text component, image always first in the DOM so the
mobile stacked view (image on top) works for both the normal and reversed
variant with one CSS reset. Category pages alternate which side the
illustration sits on based on the category's `display_order` (odd/even).
Also applied to `why-us.php` (new intro row above the existing 4-card
grid, using a new "trust/direct-line" illustration) and `about.php` (the
mission/approach section, using a new illustration built around the
brand's existing "Focus Frame" spark motif — the pull-quote card that
used to sit beside the mission text now sits full-width below the row
instead, since a 2-column media-row only fits one media + one text
column).

**Quote pre-select**: a category page's CTA links to
`quote.php?category=<slug>`. `quote.php` resolves the slug to a category
id server-side, marks the matching `<option>` `selected`, and marks every
*other* category's checkbox `<fieldset>` `hidden` — reusing the same
`hidden` mechanism `js/main.js`'s existing category-select filter already
used, so no JS changes were needed and the pre-narrowed state also works
without JS. A small note ("Pre-selected: Mobile Development — change the
category below to browse everything") makes the pre-selection visible
rather than silent.

**A real pre-existing bug found and fixed**: several service descriptions
rendered with mojibake (`ÔÇô` instead of `—`) — confirmed present on the
already-live `services.php` too, so not something this round introduced.
`database.sql` itself has correct UTF-8 em-dashes; the corruption was in
the previously-imported data. Re-imported with
`mysql --default-character-set=utf8mb4 < database.sql` and confirmed the
fix on both the old and new pages.

**A second real bug found via testing**: the new homepage cards' example-
service tags (`.svc-overview-tag`) used `white-space: nowrap` with no
`max-width`, so a long, unwrappable title (e.g. "Custom mobile app
development (Android/iOS)") could exceed the card's own width — invisible
at most breakpoints but overflowing at 1024–1280px, where the 4-column
grid gives each card its least horizontal room. Fixed with
`max-width: 100%; overflow: hidden; text-overflow: ellipsis` on the tag,
plus `min-width: 0` on `.svc-overview-card` itself (the same grid-item
shrink issue this project has hit more than once now).

Verified: PHP lint on every new/changed file. A 14-page × 8-breakpoint
sweep (360–1920px, all 6 existing pages plus all 8 new category pages) —
zero HTTP errors, zero console errors, zero horizontal overflow (this is
what caught the tag-overflow bug above). Functional checks via Playwright:
all 8 homepage cards link to the correct `services/<slug>.php`; clicking
through lands on the right page with the right H1/breadcrumb; a category
page's CTA correctly carries the slug to `quote.php` and the destination
page arrives with the right option selected and the right (and only the
right) service checklist visible; nav/footer/breadcrumb/stylesheet/script
links all resolve correctly both from root pages and from one level deep;
an unknown category slug returns a real 404. Screenshots confirm the
illustrations, alternating media-row sides, and mobile stacking all
render as intended.

## Category detail pages gained real depth (content + one new column)

Each `services/<slug>.php` grew from hero + service list + CTA into six
sections: hero, **"Why this matters"** (1-2 paragraphs on the problem the
category solves), **"Why choose Brightframe Software for X"** (3-4
honest differentiators), the service list (now with 2-3 sentence
descriptions instead of one line), an educational **"Types of X"**
comparison (e.g. native vs. cross-platform for Mobile Development,
on-page/technical/off-page for SEO), then the CTA band.

**Content honesty rules** (the actual brief for this round): no invented
client names, portfolios, or case studies; no specific years of company
history; no multi-country claims; no unverifiable superlatives ("best",
"top", "#1"); differentiators phrased honestly for a founder-led/small
operation ("you work directly with the person building it") rather than
implying a larger team. "Types of X" sections are general industry
knowledge, not claims about Brightframe. All new copy lives in a new
`includes/category-content.php` (`category_why_matters()`,
`category_differentiators()`, `category_types()`, one entry per category,
genuinely different content per category rather than a template with the
name swapped in) — its file-level comment restates these rules so they're
visible to whoever edits this content next. One nuance worth flagging:
the brief asked to reference "the founder's ~3 years" from an existing
CV/bio, but no specific year count actually appears anywhere on the
current site (checked before writing) — so the founder's background is
described qualitatively (full-stack development and enterprise systems
implementation, matching `about.php`'s existing text) rather than with an
unverifiable number. Say the word and a specific figure can be added.

**Schema change**: `services` gained a `long_description` column
(nullable — falls back to the existing short `description` if ever
unset). The original `description` stays untouched and short, since it's
still used in compact contexts (`services.php`'s card grid, the quote
form's checklist) where a 2-3 sentence paragraph per item wouldn't fit;
`long_description` is the expanded version used only on the detail pages,
which have room for it. Populated for all 38 existing services in the
seed data. Re-import required (`database.sql` does its usual drop +
rebuild + reseed).

Verified: PHP lint on every changed/new file. Re-ran the 14-page ×
8-breakpoint sweep — zero HTTP errors, zero console errors, zero
overflow. Checked all 8 pages' rendered output for stray PHP
warnings/notices (none — one false-positive grep hit was just the word
"notice" appearing naturally in the marketing copy). Re-confirmed the
quote pre-select flow and homepage card links still work after the
expansion, and that illustration side-alternation is unaffected.
Grepped the new content files for the forbidden-claims list (superlatives,
"years of experience," "our clients," "team of," etc.) — zero matches
outside the rules comment itself describing what to avoid.

## Breadcrumb removed from category detail pages

Reversal of one piece of the round above: the breadcrumb
(`Home / Services / [Category]`) on `services/<slug>.php` has been
removed, at explicit request after review. Two things were checked
before removing it, since an earlier report of "duplicate navigation" on
these same pages had turned out (after a full DOM audit) to not be a
real duplicate: confirmed via a fresh screenshot comparison that what was
being pointed at genuinely was the breadcrumb row itself, and confirmed
after removal that no other page or include still references it. The
`.breadcrumb` CSS block in `css/styles.css` was also removed — it had no
other caller once this markup was gone. Pages now flow directly from the
shared header into the hero section, matching every other standalone
page on the site.

Verified: PHP lint. Re-ran the 14-page × 8-breakpoint sweep — zero HTTP
errors, zero console errors, zero overflow, and confirmed `.breadcrumb`
no longer appears in the DOM on any of the 8 category pages.

## Reviews system, admin-editable stats, brighter theme touches

This round was requested with reference screenshots from a competitor's
live site (client logos, "client served" style stats, 5 testimonials).
Three specific pieces of that request were declined rather than built as
literally asked, since they'd have meant publishing fabricated content —
flagged to the user directly before starting, consistent with this
project's standing no-fabrication rule:

- **Invented testimonials** — asked for 5 published reviews; Brightframe
  has no clients yet, so 5 reviews would all be fiction. Built the
  underlying *system* instead (see below) and launched it with zero
  reviews, exactly as agreed.
- **"Imaginary" client/partner logos** — asked for explicitly, by that
  word. The reference screenshot was also a competitor's actual client
  list (KRA, KenGen, Madison Insurance, etc.) — reproducing a real
  company's real client roster on Brightframe's site would misrepresent
  who Brightframe has actually worked with, regardless of intent. Kept
  the existing honest "Client logo" placeholders, see below for what
  changed about them.
- **Invented stats ("fill in real numbers later")** — asked for made-up
  numbers with a promise to edit them later; declined because a public
  page shows whatever's there *now*, and "we'll fix it later" doesn't
  change what a visitor sees in the meantime. Built an admin-editable
  version instead (see below) seeded with empty values, not numbers.

**Reviews system** — new `reviews` table (`status`: pending/approved/
rejected). `reviews.php` is the public page: a grid of `status =
'approved'` reviews (empty-state message if there are none — the honest
default right now), plus a "Share your experience" submission form
(`handlers/review_handler.php`, same honeypot pattern as the other two
forms) that always lands as `'pending'`. Nothing submitted through the
form is public until approved in the new `admin/reviews.php` moderation
queue (approve / reject / unpublish, plain POST actions — no separate
handler file). Star-rating input on the form uses the classic CSS-only
technique (radio buttons + `~` sibling selector), no JS required.

**Admin-editable homepage stats** — new `site_stats` table
(`label`, `value`, `display_order`), seeded with 3 labeled rows and
**no values**. `includes/site-stats.php` is the public display
component: it queries only non-empty values and renders nothing at all
if none exist yet — not a placeholder, not a zero, nothing. `admin/
stats.php` is a simple label/value editor; the section will start
appearing on the homepage the moment a real number goes in, no code
changes needed.

**Theme brightness pass** — three bounded, honest changes rather than a
full recolor (which would need its own proposal-first cycle, per this
project's standing rule for visual-identity changes): the footer now
uses a richer blue gradient distinct from the near-black `--navy` used
elsewhere (`css/styles.css`, scoped to `footer` only); the trust-logo
strip is now an infinite auto-scrolling marquee (`prefers-reduced-motion`
falls back to the previous manually-scrollable strip) — same honest
"Client logo" placeholder content, just more visually alive; and a new
"Built for every screen" homepage section shows 3 of the existing
category illustrations (web, mobile, ERP dashboard) cross-fading
automatically, as an original-illustration answer to "show it working on
laptop and phone" without real product screenshots that don't exist yet
or stock photos of strangers (both against this project's standing image
rules).

Verified: PHP lint on every new/changed file. Full regression sweep
across all pages including the two new admin pages and reviews.php —
zero HTTP errors, zero console errors, zero overflow. Full functional
round-trip via Playwright: submitted a real review through the public
form, confirmed it appeared in the admin pending queue and nowhere on
the public site yet, approved it, confirmed it then appeared on
reviews.php; edited a stat value in admin/stats.php, confirmed the
homepage stats section appeared with the real value, then cleared it
back to empty and confirmed the section disappeared again. All test data
(the test review, the test stat value) was removed after verifying —
the database was left in the same empty, honest state it started in.

## Admin login — the panel finally requires one

Every `admin/*.php` page now requires a real session login instead of
being open to anyone with the URL. Two new tables: `admin_users`
(bcrypt password hash, seeded with one user — see the comment above it
in `database.sql` for the username and how to change the password) and
`login_attempts` (rolling log for rate limiting). `admin_users` is
deliberately exempt from this file's usual drop-and-rebuild-on-reimport
pattern — every other table gets wiped and reseeded when `database.sql`
is re-run, which would be fine for catalog/lead data but would silently
reset the admin password back to the seed value every time otherwise.

`includes/admin-auth.php` is a one-line-to-use guard (`require_once` at
the top of a page, before any output) that redirects to `admin/login.php`
if the session isn't authenticated; all three existing admin pages
(`submissions.php`, `reviews.php`, `stats.php`) now start with it, and
their "Unprotected page" warning banners are gone, replaced by a
"Logged in as X · Log out" bar (`includes/admin-bar.php`).

`admin/login.php` itself: CSRF token, a generic error message regardless
of whether the username or password was wrong (no enumeration hint),
IP-based rate limiting (5 failed attempts / 15 minutes — checked
*before* even a correct password is accepted, so a lockout can't be
raced), `session_regenerate_id(true)` on success, `httponly` +
`samesite=Lax` session cookie, and a `?redirect=` param (strictly
pattern-validated against `^[a-zA-Z0-9_-]+\.php$`, so it can't become an
open redirect) that sends you back to whatever admin page you originally
tried to reach. `admin/logout.php` clears the session and its cookie.

Verified via Playwright, using real HTTP requests (not just reading the
code): all three admin pages redirect to login when logged out; a wrong
password fails with the generic message and no session is granted; the
correct password logs in, shows the userbar, and grants access to all
three pages; logging out revokes access again; the `?redirect=` param
round-trips correctly; 6 failed attempts in a row triggers the lockout
message, and — the case that actually matters — the *correct* password
is also rejected while that IP is locked out. Full site regression sweep
(all public pages × 4 breakpoints, plus all 3 admin pages logged in)
afterward: zero HTTP errors, zero console errors, zero overflow. All
test rows (failed login_attempts, the wrong-password ones included) were
cleared from the database after verifying, so nothing is left locked out
or polluted.

## Admin panel rebuilt into a real internal dashboard

The 3-page admin panel (a login gate bolted onto one big submissions
table) became an 8-page dashboard: sidebar navigation, a landing page
with at-a-glance counts, dedicated list + detail views for quotes and
messages with real status pipelines, and a new Services CRUD page — see
"Admin view" above for what each page does. `admin/submissions.php` and
`includes/admin-bar.php` were removed, fully superseded by the new
per-section pages and the sidebar's built-in user card.

**Schema**: `quote_requests.status` gained `'in_discussion'` (between
`'contacted'` and `'closed'`). `contact_messages.status` changed shape
entirely — it used to share `quote_requests`' new/contacted/closed
values, but a message doesn't get "contacted", it gets "replied", so it's
now its own new/read/replied enum. `reviews` gained `reject_reason`
(admin-facing only, never shown publicly).

**Security**: `includes/admin-auth.php` now also issues a per-session
CSRF token and exposes `admin_csrf_check()`; every admin POST handler
(quote/message status changes, review moderation, services CRUD, stat
tile edits) calls it before touching the database, and every admin form
carries the token as a hidden field.

**Shared layout**: `includes/admin-layout-header.php` /
`admin-layout-footer.php` mirror the public site's own header.php/
footer.php split — a dark sidebar (per-section icons, badge counts for
new quotes/messages/pending reviews, and a user card with an avatar
initial, username, "Administrator" label, and a log-out icon) plus a
slim topbar above the content area (hamburger toggle, current section
name, a "View site" link, and the avatar again), reusing the site's
actual navy/indigo/cyan palette and type system rather than a generic
admin scaffold. Collapses to a hamburger-triggered drawer under 900px,
same interaction pattern as the public nav's mobile menu.

**Services CRUD** (`admin/services.php`): adding a category now also
auto-creates its dedicated `services/<slug>.php` detail page — a 3-line
shim that sets `$categorySlug` and includes the new shared
`includes/category-page.php` renderer (which pulls the category and its
services straight from the database, including a generic fallback intro/
"why it matters" via `includes/category-content.php` for categories that
don't have hand-written editorial copy yet). This closes the gap from
earlier in the build, where a new category only ever got a
`services.php#cat-<slug>` anchor link — new categories now get a real
page immediately. `includes/icons.php`'s `category_detail_url()` still
exists as a defensive fallback (checks the file actually exists on disk
before linking to it) for the edge case of a slug created outside this
form. Renaming an *existing* category's slug still breaks its existing
detail page, since that file is keyed to the old slug on disk — flagged
inline on the edit form. One known gap: deleting a category removes the
database rows but does **not** delete its generated `services/<slug>.php`
file, so a deleted category can leave an orphaned (unreachable, harmless)
page file behind — worth a manual cleanup if you delete a category, or a
follow-up if this becomes annoying. `icon_name` is intentionally not a
field on the add/edit-service form — the established convention (every
service in a category shares one icon, keyed by the category's slug) is
preserved by auto-setting it server-side on every save, not exposed as
free text an admin could mistype.

**Site Stats page** does two genuinely different things, kept visually
and textually separate on the page: real analytics (KPI cards for total
quotes/messages/reviews-by-status/categories/services, plus a plain-div
submissions-per-week bar chart — no charting library needed for 8 bars)
computed live from the database every page load, and the pre-existing
homepage stat-tile editor below it. The analytics are automatic and
admin-only; the tiles are manual and public — it's expected and correct
for the analytics to show real activity while the public tiles stay
empty, if there's nothing worth publishing yet.

**Two real bugs caught during testing, both fixed:**
1. Adding a category with the slug field left blank (the normal case —
   it's meant to auto-generate from the name) failed with "could not
   generate a slug from that name," even for perfectly normal names. Cause:
   `slugify($_POST['slug'] ?? $name)` — the slug input is always present
   in the submitted form (just empty when left blank), so `??` never
   actually reached its fallback; an empty string satisfies `isset()`, it
   isn't null. Fixed by explicitly checking `trim(...) !== ''` before
   deciding whether to fall back to the name.
2. `admin/stats.php`'s 8-bar weekly chart overflowed the page horizontally
   on a 360px-wide phone (8 columns don't fit). Fixed the same way the
   public site's mobile logo strip already handles the same shape of
   problem: `overflow-x: auto` on the chart container below 560px rather
   than squeezing or breaking the layout — confirmed after the fix that
   the *page* no longer scrolls horizontally, only the chart does.

Verified: PHP lint on every admin page and every include it touches. CSS
audited class-by-class against the new sidebar/topbar/dashboard markup —
every class referenced actually has a rule. Explicit auth audit (the
user's request) — all 8 protected admin pages checked individually via
real HTTP requests with no session cookie, confirmed each redirects to
`login.php`: `index.php`, `quotes.php`, `quote-view.php`, `messages.php`,
`message-view.php`, `reviews.php`, `services.php`, `stats.php`. Full
public-to-admin integration test via a real Playwright browser session
(logged in through the actual login form, not a seeded session) —
submitted a real quote request, contact message, and review through
their public (fetch-based) forms; confirmed all three appeared
immediately in their respective admin list pages; changed a quote's
status and confirmed it persisted and displayed; confirmed a viewed
message auto-advances new → read; approved the review in the admin queue
and **confirmed it then appeared on the public reviews.php page** — the
specific connection the user asked to have verified, not just assumed.
Also exercised the full Services CRUD cycle (add category → confirmed
its new `services/<slug>.php` page loads with HTTP 200 → add a service →
delete it → delete the category) and the Site Stats page (KPI cards and
the 8-column bar chart both render from real data). Full regression
sweep: all 8 admin pages checked unauthenticated, then the full flow
above while logged in, then all 6 sidebar admin pages re-checked at a
360px mobile viewport for horizontal overflow — 34/34 automated checks
passed, zero console errors. Every row of test data created during this
pass (the test quote, message, review, category, and service, plus the
one orphaned test category page file the delete-category gap above
leaves behind) was removed afterward; the database was confirmed back to
its pre-test state before finishing.
