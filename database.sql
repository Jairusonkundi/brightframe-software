-- =====================================================================
-- Brightframe Software — database schema
-- Import this file directly via phpMyAdmin (or `mysql -u root -p < database.sql`)
--
-- SCHEMA CHANGE (this version): the old flat `contact_submissions` table
-- (one service per lead, via a nullable service_id/service_needed pair)
-- is replaced by `quote_requests` + a `quote_request_services` junction
-- table, matching the rebuilt "Request a quote" form: multiple services
-- per request, plus phone/budget_range/timeline fields that didn't exist
-- before. `contact_submissions` is dropped — its shape doesn't map
-- cleanly onto the new one (multi-service vs. single-service), so this is
-- a clean break rather than a column-by-column migration. If you have
-- real leads in the old table, export them first; this file does not
-- preserve them.
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
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id    INT UNSIGNED NOT NULL,
  title          VARCHAR(150) NOT NULL,
  description    TEXT NOT NULL,
  icon_name      VARCHAR(80) NOT NULL,
  display_order  INT NOT NULL DEFAULT 0,
  INDEX idx_category_id (category_id),
  CONSTRAINT fk_services_category FOREIGN KEY (category_id)
    REFERENCES service_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Drop the old single-service contact form table and its would-be
-- replacement, then rebuild clean (see the note at the top of this file).
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS quote_request_services;
DROP TABLE IF EXISTS contact_submissions;
DROP TABLE IF EXISTS quote_requests;

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
  status           ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
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
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Full-stack web application development', 'Frontend, backend, and database — built end-to-end as one working system.', 'web-development-design', 1),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Website design & UI/UX development', 'Interfaces designed around how your users actually think and click.', 'web-development-design', 2),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'E-commerce website development', 'Online stores built to convert browsers into buyers.', 'web-development-design', 3),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Website redesign & modernization', 'Bring an aging site up to modern speed, security, and design.', 'web-development-design', 4),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Progressive web apps (PWA)', 'App-like experiences that load fast and work offline.', 'web-development-design', 5),
((SELECT id FROM service_categories WHERE slug = 'web-development-design'), 'Landing page design', 'Focused, high-converting pages for a single campaign or offer.', 'web-development-design', 6);

-- 2. Mobile Development
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'Custom mobile app development (Android/iOS)', 'Native apps built specifically for Android or iOS.', 'mobile-development', 1),
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'Cross-platform app development', 'One codebase, shipped to Android and iOS together.', 'mobile-development', 2),
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'Mobile app UI/UX design', 'Interfaces designed for thumbs, small screens, and short attention spans.', 'mobile-development', 3),
((SELECT id FROM service_categories WHERE slug = 'mobile-development'), 'App maintenance & updates', 'Ongoing fixes and OS updates that keep your app live in the stores.', 'mobile-development', 4);

-- 3. Software & Systems Development
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Custom business software development', 'Purpose-built software for how your business actually operates.', 'software-systems-development', 1),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Backend & API development', 'Secure, well-structured APIs built for clean data flow.', 'software-systems-development', 2),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Database design & architecture', 'Relational structures built for integrity and performance at scale.', 'software-systems-development', 3),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Systems integration', 'Connecting your software to banks, statutory bodies, and other APIs.', 'software-systems-development', 4),
((SELECT id FROM service_categories WHERE slug = 'software-systems-development'), 'Software maintenance & support', 'Ongoing updates and fixes that keep your software running.', 'software-systems-development', 5);

-- 4. ERP & Business Systems
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'ERP implementation & configuration', 'An ERP platform configured around your actual workflows.', 'erp-business-systems', 1),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'HR & payroll system development', 'HR and payroll tools that match your policies, not a template.', 'erp-business-systems', 2),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'CRM setup & customization', 'A CRM configured around your real sales process.', 'erp-business-systems', 3),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'Business process automation', 'Replace manual, repetitive workflows with automated ones.', 'erp-business-systems', 4),
((SELECT id FROM service_categories WHERE slug = 'erp-business-systems'), 'Inventory & asset management systems', 'Track stock and assets accurately, in real time.', 'erp-business-systems', 5);

-- 5. Branding
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Logo & visual identity design', 'A distinct visual identity that''s recognizable at a glance.', 'branding', 1),
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Brand strategy & positioning', 'Clarify who you serve and what makes you different.', 'branding', 2),
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Brand guidelines & style guides', 'A reference that keeps your brand consistent everywhere.', 'branding', 3),
((SELECT id FROM service_categories WHERE slug = 'branding'), 'Business collateral design', 'Business cards, letterheads, and templates that match your brand.', 'branding', 4);

-- 6. Digital Marketing
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Social media marketing & management', 'Consistent, on-brand social presence, planned and managed.', 'digital-marketing', 1),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Content marketing', 'Content built to attract and inform your audience.', 'digital-marketing', 2),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Email marketing campaigns', 'Campaigns that nurture leads and keep customers coming back.', 'digital-marketing', 3),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Pay-per-click (PPC) advertising', 'Paid campaigns optimized to bring in leads, not waste spend.', 'digital-marketing', 4),
((SELECT id FROM service_categories WHERE slug = 'digital-marketing'), 'Digital marketing strategy & consulting', 'A clear, prioritized plan built around your goals and budget.', 'digital-marketing', 5);

-- 7. SEO
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'seo'), 'On-page SEO optimization', 'Your content and structure tuned to rank for searches that matter.', 'seo', 1),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'Technical SEO audits', 'A technical review that finds what''s holding your rankings back.', 'seo', 2),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'Local SEO', 'Show up in local search and maps when nearby customers look for you.', 'seo', 3),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'SEO content strategy', 'A content plan built around what your customers actually search.', 'seo', 4),
((SELECT id FROM service_categories WHERE slug = 'seo'), 'Keyword research & competitor analysis', 'Know what customers search for and where competitors beat you.', 'seo', 5);

-- 8. IT & Technical Consulting
INSERT INTO services (category_id, title, description, icon_name, display_order) VALUES
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'Technology strategy consulting', 'An outside, technical view on what to build, buy, or fix next.', 'it-technical-consulting', 1),
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'Digital transformation consulting', 'A practical roadmap for moving manual processes online.', 'it-technical-consulting', 2),
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'IT infrastructure consulting', 'An honest assessment of your servers, networks, and hosting.', 'it-technical-consulting', 3),
((SELECT id FROM service_categories WHERE slug = 'it-technical-consulting'), 'System architecture consulting', 'A second opinion on your system design before you build.', 'it-technical-consulting', 4);
