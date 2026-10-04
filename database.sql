-- =====================================================================
-- Brightframe Software — database schema
-- Import this file directly via phpMyAdmin (or `mysql -u root -p < database.sql`)
--
-- SCHEMA CHANGE (this version): richer admin status pipelines, to match
-- the rebuilt admin panel (admin/quotes.php, admin/messages.php).
-- quote_requests.status gained 'in_discussion' (between 'contacted' and
-- 'closed'). contact_messages.status changed shape entirely — it used
-- to share quote_requests' new/contacted/closed values, but the admin
-- panel now tracks it separately as new/read/replied (a message doesn't
-- get "contacted", it gets "replied"). reviews gained `reject_reason`
-- (admin-facing only, never shown publicly) — an optional note on why a
-- review was turned down.
--
-- (Earlier history: two new tables backed a real admin login —
-- `admin_users` and `login_attempts` — see the comment above
-- `admin_users` below for why it's exempt from this file's usual
-- drop-and-rebuild. Before that, services gained a `long_description`
-- column — 2-3 sentence expanded copy for the dedicated
-- services/<slug>.php detail pages, alongside the original short
-- `description` still used in compact contexts. Before that, "Request a
-- quote" and "Contact Us" became two separate forms/tables —
-- quote_requests + quote_request_services, and contact_messages —
-- replacing an older
-- single-service contact_submissions table that no longer exists.)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS brightframe_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE brightframe_db;

-- ---------------------------------------------------------------------
-- Drop + rebuild the catalog tables (safe: no lead data lives here)
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS service_categories;

CREATE TABLE service_categories (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  slug           VARCHAR(150) NOT NULL UNIQUE,
  display_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE services (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id       INT UNSIGNED NOT NULL,
  title             VARCHAR(150) NOT NULL,
  description       TEXT NOT NULL,
  long_description  TEXT NULL, -- 2-3 sentence expanded copy for services/<slug>.php; `description` stays short for compact contexts (services.php cards, homepage tags)
  icon_name         VARCHAR(80) NOT NULL,
  display_order     INT NOT NULL DEFAULT 0,
  INDEX idx_category_id (category_id),
  CONSTRAINT fk_services_category FOREIGN KEY (category_id)
    REFERENCES service_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Drop + rebuild the two lead-capture tables (see the note at the top of
-- this file). contact_submissions is dropped defensively in case this is
-- being run against a database from before the quote_requests migration.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS quote_request_services;
DROP TABLE IF EXISTS contact_submissions;
DROP TABLE IF EXISTS quote_requests;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS site_stats;

-- ---------------------------------------------------------------------
-- quote_requests: leads captured from the "Request a quote" form
-- ---------------------------------------------------------------------
CREATE TABLE quote_requests (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name             VARCHAR(150) NOT NULL,
  email            VARCHAR(150) NOT NULL,
  phone            VARCHAR(40) NOT NULL,
  project_details  TEXT NOT NULL,
  budget_range     VARCHAR(60) DEFAULT NULL,
  timeline         VARCHAR(60) DEFAULT NULL,
  submitted_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status           ENUM('new', 'contacted', 'in_discussion', 'closed') NOT NULL DEFAULT 'new',
  INDEX idx_status (status),
  INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- quote_request_services: junction table — a quote request can name
-- several services. service_id softly references services.id (no FK
-- constraint, same reasoning as elsewhere in this schema: deleting or
-- renaming a service later must never block or corrupt a historical
-- lead). service_title snapshots the service's name at submission time,
-- so old requests still read correctly even if the catalog changes.
-- ---------------------------------------------------------------------
CREATE TABLE quote_request_services (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  quote_request_id   INT UNSIGNED NOT NULL,
  service_id         INT UNSIGNED DEFAULT NULL,
  service_title      VARCHAR(150) NOT NULL,
  INDEX idx_quote_request (quote_request_id),
  INDEX idx_service_id (service_id),
  CONSTRAINT fk_qrs_quote FOREIGN KEY (quote_request_id)
    REFERENCES quote_requests(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- contact_messages: general inquiries from the simple "Contact Us" form
-- (contact.php) — deliberately separate from quote_requests. No service
-- selection, no budget/timeline: just someone with a question.
-- ---------------------------------------------------------------------
CREATE TABLE contact_messages (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  email          VARCHAR(150) NOT NULL,
  subject        VARCHAR(150) NOT NULL,
  message        TEXT NOT NULL,
  submitted_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status         ENUM('new', 'read', 'replied') NOT NULL DEFAULT 'new',
  INDEX idx_status (status),
  INDEX idx_submitted_at (submitted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- reviews: testimonials submitted via reviews.php's "Share your
-- experience" form. Nothing is public until an admin approves it
-- (admin/reviews.php) — status starts at 'pending' and the public page
-- only ever queries WHERE status = 'approved'. No seed data on purpose:
-- Brightframe has no completed client reviews yet, so this table starts
-- empty rather than pre-filled with invented testimonials. reject_reason
-- is admin-facing only (an internal note on why something was turned
-- down) — never shown anywhere on the public site.
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  company        VARCHAR(150) DEFAULT NULL,
  rating         TINYINT UNSIGNED NOT NULL,
  review_text    TEXT NOT NULL,
  status         ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  reject_reason  VARCHAR(255) DEFAULT NULL,
  display_order  INT NOT NULL DEFAULT 0,
  submitted_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_status (status),
  CONSTRAINT chk_reviews_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- site_stats: admin-editable homepage stat tiles (e.g. "Projects
-- completed", "Clients served"). `value` starts empty for every row —
-- deliberately not seeded with numbers, since Brightframe doesn't have
-- a track record to report yet. includes/site-stats.php (the public
-- display component) only renders a tile once its value is non-empty,
-- and hides the whole section if every value is still empty — so
-- nothing false ever displays; it just silently shows nothing until an
-- admin fills real numbers in via admin/stats.php.
-- ---------------------------------------------------------------------
CREATE TABLE site_stats (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  label          VARCHAR(100) NOT NULL,
  value          VARCHAR(50) DEFAULT NULL,
  display_order  INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO site_stats (label, value, display_order) VALUES
('Projects completed', NULL, 1),
('Clients served', NULL, 2),
('Businesses using our systems', NULL, 3);

-- ---------------------------------------------------------------------
-- admin_users: login credentials for admin/login.php.
--
-- Deliberately NOT dropped/rebuilt like every table above — this file is
-- normally re-run wholesale after any schema change (see the testing
-- notes elsewhere in this project), and doing that to this table would
-- silently reset the admin password back to the seed value below every
-- single time, undoing any password change with no warning. Uses
-- CREATE TABLE IF NOT EXISTS + an existence-checked INSERT instead, so
-- re-running this file is safe: the table and seed user are only created
-- once, and never touched again on subsequent imports.
--
-- Seed login: username "jairus". The password hash below corresponds to
-- the password provided directly by the site owner when this was set
-- up — change it any time via `UPDATE admin_users SET password_hash =
-- <new hash> WHERE username = 'jairus';` (generate a new hash with
-- `password_hash($newPassword, PASSWORD_DEFAULT)` in PHP).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_users (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username       VARCHAR(60) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO admin_users (username, password_hash)
SELECT 'jairus', '$2y$10$P066ABqUZH22szs3kT2qSew5NcgPX83muAyucgG2mJbTP50gfXTS.'
WHERE NOT EXISTS (SELECT 1 FROM admin_users WHERE username = 'jairus');

-- ---------------------------------------------------------------------
-- login_attempts: lightweight IP-based rate limiting for admin/login.php
-- (blocks further attempts from an IP after too many failures in a short
-- window). Just a rolling log — safe to drop/rebuild like the tables
-- above, unlike admin_users.
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS login_attempts;
CREATE TABLE login_attempts (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ip_address    VARCHAR(45) NOT NULL,
  attempted_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Seed: 8 categories
-- ---------------------------------------------------------------------
INSERT INTO service_categories (name, slug, display_order) VALUES
('Web Development & Design',      'web-development-design',      1),
('Mobile Development',            'mobile-development',          2),
('Software & Systems Development','software-systems-development',3),
('ERP & Business Systems',        'erp-business-systems',        4),
('Branding',                      'branding',                    5),
('Digital Marketing',             'digital-marketing',           6),
('SEO',                           'seo',                         7),
('IT & Technical Consulting',     'it-technical-consulting',     8);

-- ---------------------------------------------------------------------
-- Seed: 38 services, icon_name = owning category's slug
-- ---------------------------------------------------------------------

-- 1. Web Development & Design
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Full-stack web application development', 'Frontend, backend, and database — built end-to-end as one working system.', 'A web application involves more than a good-looking interface — it needs a reliable backend handling logic and a database structured to grow with your data. We build all three layers as one connected system, from the first wireframe through to a working, deployed product, so nothing gets lost in translation between "design" and "build."', 'web-development-design', 1),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Website design & UI/UX development', 'Interfaces designed around how your users actually think and click.', 'Good design isn''t just about how a page looks — it''s about whether someone can find what they need without thinking twice. We design interfaces around how your specific users actually behave, and work through the flow before it becomes expensive to change.', 'web-development-design', 2),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'E-commerce website development', 'Online stores built to convert browsers into buyers.', 'Online stores have their own requirements: a product catalog that''s easy to manage, a checkout flow that doesn''t lose customers halfway through, and secure payment handling. We build stores structured to convert browsers into buyers, with an admin side that''s straightforward for your team to run day to day.', 'web-development-design', 3),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Website redesign & modernization', 'Bring an aging site up to modern speed, security, and design.', 'An older site can quietly become a liability — slow to load, awkward on mobile, or built on technology that''s no longer well supported. We assess what''s worth keeping, rebuild what isn''t, and bring the result up to modern speed, security, and design standards without starting completely from scratch where it isn''t necessary.', 'web-development-design', 4),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Progressive web apps (PWA)', 'App-like experiences that load fast and work offline.', 'A PWA behaves like a native app — fast to load, able to work with a patchy connection, and addable to a home screen — while still being built and maintained as a website. It''s a practical middle ground when a full native app isn''t justified yet but a plain website isn''t quite enough.', 'web-development-design', 5),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Landing page design', 'Focused, high-converting pages for a single campaign or offer.', 'A landing page has one job: convert visitors for a specific campaign or offer, without the distractions of a full site. We design these around a single clear action, so traffic from ads or campaigns has somewhere effective to land.', 'web-development-design', 6);

-- 2. Mobile Development
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'Custom mobile app development (Android/iOS)', 'Native apps built specifically for Android or iOS.', 'Built specifically for the platform you need — Android, iOS, or both — using each platform''s own tools and conventions rather than a generic template. That means an interface and a level of performance that feels native to the device it''s running on.', 'mobile-development', 1),
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'Cross-platform app development', 'One codebase, shipped to Android and iOS together.', 'One codebase, shipped to Android and iOS together, using frameworks built for exactly this — so you get both platforms without paying to build and maintain two separate apps in parallel.', 'mobile-development', 2),
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'Mobile app UI/UX design', 'Interfaces designed for thumbs, small screens, and short attention spans.', 'Interfaces designed for thumbs, small screens, and short attention spans — very different constraints from designing for a desktop browser. We design and work through the flow before development starts, so the build isn''t guessing at what users actually need.', 'mobile-development', 3),
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'App maintenance & updates', 'Ongoing fixes and OS updates that keep your app live in the stores.', 'Apps don''t stay finished — operating systems update, devices change, and small bugs surface once real users start using it. Ongoing maintenance keeps your app live in the stores and working properly as Android and iOS move forward underneath it.', 'mobile-development', 4);

-- 3. Software & Systems Development
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Custom business software development', 'Purpose-built software for how your business actually operates.', 'Purpose-built software designed around how your business actually operates, rather than a generic tool you have to adapt your process to fit. We start by understanding the real workflow before writing a line of code.', 'software-systems-development', 1),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Backend & API development', 'Secure, well-structured APIs built for clean data flow.', 'The logic and data layer behind an application — secure, well-structured APIs that your website, mobile app, or other systems can rely on. Built for clean, predictable data flow rather than ad-hoc endpoints added as an afterthought.', 'software-systems-development', 2),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Database design & architecture', 'Relational structures built for integrity and performance at scale.', 'The database is the part almost nobody sees and almost everything depends on. We design relational structures for data integrity and performance at scale, so queries stay fast and the data stays trustworthy as it grows.', 'software-systems-development', 3),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Systems integration', 'Connecting your software to banks, statutory bodies, and other APIs.', 'Most businesses already run several tools — accounting software, payment providers, statutory or banking APIs — that don''t talk to each other by default. We connect them, so data moves automatically instead of being re-entered by hand.', 'software-systems-development', 4),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Software maintenance & support', 'Ongoing updates and fixes that keep your software running.', 'Software needs upkeep after launch: dependency updates, bug fixes, and small adjustments as your business changes. Ongoing maintenance keeps it running reliably instead of slowly falling behind.', 'software-systems-development', 5);

-- 4. ERP & Business Systems
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'ERP implementation & configuration', 'An ERP platform configured around your actual workflows.', 'An ERP platform is only as useful as its configuration. We set it up around your actual workflows — chart of accounts, inventory structure, approval flows — rather than leaving you to adapt to its defaults.', 'erp-business-systems', 1),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'HR & payroll system development', 'HR and payroll tools that match your policies, not a template.', 'HR and payroll tools built or configured to match your actual policies — leave structures, statutory deductions, approval chains — instead of a generic template that needs constant manual correction.', 'erp-business-systems', 2),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'CRM setup & customization', 'A CRM configured around your real sales process.', 'A CRM configured around your real sales process, not a theoretical one — the stages, fields, and reports that reflect how your team actually sells, so the data in it stays accurate enough to be useful.', 'erp-business-systems', 3),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'Business process automation', 'Replace manual, repetitive workflows with automated ones.', 'Manual, repetitive workflows — approvals, notifications, data entry between systems — replaced with automated ones, so staff spend time on judgment calls instead of repetitive administrative steps.', 'erp-business-systems', 4),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'Inventory & asset management systems', 'Track stock and assets accurately, in real time.', 'Track stock and assets accurately, in real time, instead of relying on periodic manual counts. Built to reflect what''s actually happening in your business, not just what was true at last quarter''s audit.', 'erp-business-systems', 5);

-- 5. Branding
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Logo & visual identity design', 'A distinct visual identity that''s recognizable at a glance.', 'A distinct visual identity that''s recognizable at a glance — not just a logo in isolation, but a consistent system of color, type, and imagery that holds together across everywhere your business appears.', 'branding', 1),
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Brand strategy & positioning', 'Clarify who you serve and what makes you different.', 'Before any visuals get made, we clarify who you''re actually serving and what makes you different from the alternatives they''re considering — decisions that should drive the design, not follow it.', 'branding', 2),
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Brand guidelines & style guides', 'A reference that keeps your brand consistent everywhere.', 'A practical reference document — logo usage, color codes, typography rules — that keeps your brand consistent even as new materials get produced by different people over time.', 'branding', 3),
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Business collateral design', 'Business cards, letterheads, and templates that match your brand.', 'Business cards, letterheads, email signatures, and templates that match your brand properly, rather than being reinvented inconsistently each time something new is needed.', 'branding', 4);

-- 6. Digital Marketing
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Social media marketing & management', 'Consistent, on-brand social presence, planned and managed.', 'Consistent, on-brand social presence — planned content, not sporadic posting — managed around what actually resonates with your specific audience rather than generic best practices.', 'digital-marketing', 1),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Content marketing', 'Content built to attract and inform your audience.', 'Content built to attract and inform your audience — genuinely useful material that earns attention, rather than promotional content that gets scrolled past.', 'digital-marketing', 2),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Email marketing campaigns', 'Campaigns that nurture leads and keep customers coming back.', 'Campaigns that nurture leads and keep existing customers coming back, sent to a list that''s actually opted in and segmented, rather than a single generic blast to everyone.', 'digital-marketing', 3),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Pay-per-click (PPC) advertising', 'Paid campaigns optimized to bring in leads, not waste spend.', 'Paid campaigns optimized to bring in qualified leads, not just clicks — budget spent deliberately on the audiences and keywords likely to actually convert.', 'digital-marketing', 4),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Digital marketing strategy & consulting', 'A clear, prioritized plan built around your goals and budget.', 'A clear, prioritized plan built around your specific goals and budget — which channels are worth the investment right now, and which aren''t yet, rather than a generic checklist.', 'digital-marketing', 5);

-- 7. SEO
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'seo'), 'On-page SEO optimization', 'Your content and structure tuned to rank for searches that matter.', 'Your content and page structure tuned to rank for the searches that actually matter to your business — titles, headings, and internal linking, not just keyword stuffing.', 'seo', 1),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'Technical SEO audits', 'A technical review that finds what''s holding your rankings back.', 'A technical review that finds what''s actually holding your rankings back — site speed, crawl errors, mobile issues — the foundational problems that content improvements alone can''t fix.', 'seo', 2),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'Local SEO', 'Show up in local search and maps when nearby customers look for you.', 'Getting your business to show up in local search and Maps results when nearby customers are looking for exactly what you offer — accurate listings, consistent business information, and genuine local relevance signals.', 'seo', 3),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'SEO content strategy', 'A content plan built around what your customers actually search.', 'A content plan built around what your customers are actually searching for, not just what''s easy to write about — so new content has a real chance of being found.', 'seo', 4),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'Keyword research & competitor analysis', 'Know what customers search for and where competitors beat you.', 'Understanding what your customers actually search for, and where your current competitors are outranking you and why — the research that should inform a strategy, not follow it.', 'seo', 5);

-- 8. IT & Technical Consulting
INSERT INTO services (category_id, title, description, long_description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'Technology strategy consulting', 'An outside, technical view on what to build, buy, or fix next.', 'An outside, technical view on what to build, buy, or fix next — help prioritizing technical decisions against your actual goals and budget, not a generic best-practices checklist.', 'it-technical-consulting', 1),
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'Digital transformation consulting', 'A practical roadmap for moving manual processes online.', 'A practical roadmap for moving manual, paper-based, or spreadsheet-driven processes online — sequenced realistically, rather than trying to change everything at once.', 'it-technical-consulting', 2),
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'IT infrastructure consulting', 'An honest assessment of your servers, networks, and hosting.', 'An honest assessment of your servers, networks, and hosting setup — what''s working, what''s a risk, and what''s worth changing, based on your actual scale and budget rather than a generic checklist.', 'it-technical-consulting', 3),
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'System architecture consulting', 'A second opinion on your system design before you build.', 'A second opinion on your system design before you build — catching scalability, security, or integration issues while they''re still a conversation, not a costly rewrite.', 'it-technical-consulting', 4);
