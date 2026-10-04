-- =====================================================================
-- Brightframe Software — seed data
-- Realistic reviews, quote requests, and contact messages for the admin panel.
--
-- Run AFTER database.sql, ideally on a fresh import (database.sql drops and
-- recreates quote_requests, contact_messages, and reviews, so running this
-- once right after the schema import gives one clean set of sample data).
-- The stats UPDATEs at the bottom are idempotent and safe to re-run.
-- =====================================================================

USE brightframe_db;

-- =====================================================================
-- Quote Requests (12 realistic leads across various statuses)
-- =====================================================================
INSERT INTO quote_requests (name, email, phone, project_details, budget_range, timeline, submitted_at, status) VALUES
('Grace Wanjiku', 'grace.wanjiku@savannatech.co.ke', '+254 712 345 678',
 'We need a modern e-commerce platform for our electronics retail business. Currently selling through Instagram DMs and WhatsApp, which is unsustainable at our scale. Need product catalog, M-Pesa integration, delivery tracking, and an admin dashboard for inventory.',
 'KES 500,000 - 1,000,000', '2-3 months',
 DATE_SUB(NOW(), INTERVAL 12 DAY), 'new'),

('David Ochieng', 'david@uplandsbrewing.co.ke', '+254 723 456 789',
 'Our brewery needs a custom inventory and distribution management system. We track over 50 SKUs across 200+ bars and restaurants, currently using spreadsheets. Need real-time stock levels, order management, and delivery route optimization.',
 'KES 1,000,000 - 2,000,000', '3-4 months',
 DATE_SUB(NOW(), INTERVAL 10 DAY), 'new'),

('Amina Hassan', 'amina@swahilitechstartup.com', '+254 734 567 890',
 'Looking for a cross-platform mobile app for our fintech startup. The app handles micro-loans for small traders — application, credit scoring, disbursement, and repayment tracking via M-Pesa. Need both Android and iOS from a single codebase.',
 'KES 2,000,000 - 3,000,000', '4-6 months',
 DATE_SUB(NOW(), INTERVAL 8 DAY), 'contacted'),

('James Kamau', 'james.kamau@highlandfarmltd.co.ke', '+254 745 678 901',
 'We run a dairy farm with 200+ cows and need an ERP system to manage everything — feed inventory, milking records, veterinary schedules, milk collection and delivery to Brookside. Currently all on paper and it is chaos.',
 'KES 1,500,000 - 2,500,000', '3-5 months',
 DATE_SUB(NOW(), INTERVAL 7 DAY), 'in_discussion'),

('Fatima Ali', 'fatima@safaridiscounts.co.ke', '+254 700 123 456',
 'Need a complete brand refresh and new website. Our current site looks like it was built in 2015. We are a travel agency specializing in East African safaris and need a visually stunning site with booking integration.',
 'KES 300,000 - 600,000', '6-8 weeks',
 DATE_SUB(NOW(), INTERVAL 5 DAY), 'new'),

('Peter Njoroge', 'peter@njoroge-law.co.ke', '+254 711 234 567',
 'Our law firm needs a case management system. Track cases by client, court dates, document deadlines, billing hours. Need a client portal so clients can check their case status without calling us every day.',
 'KES 800,000 - 1,200,000', '2-3 months',
 DATE_SUB(NOW(), INTERVAL 4 DAY), 'contacted'),

('Sarah Chebet', 'sarah@tuitionhub.co.ke', '+254 722 345 678',
 'We are building an EdTech platform for secondary school students in Kenya. Need video lessons, quizzes, progress tracking, and a parent dashboard. Looking at React Native for the mobile app and Node.js backend.',
 'KES 3,000,000 - 5,000,000', '6-8 months',
 DATE_SUB(NOW(), INTERVAL 3 DAY), 'new'),

('Michael Otieno', 'michael@otssecurity.co.ke', '+254 733 456 789',
 'Our security company manages guards across multiple properties. Need a mobile app for guard check-in (QR code scanning at patrol points), incident reporting, shift scheduling, and a client portal showing real-time guard status.',
 'KES 1,200,000 - 1,800,000', '3-4 months',
 DATE_SUB(NOW(), INTERVAL 2 DAY), 'new'),

('Lucy Wambui', 'lucy@greenacres.co.ke', '+254 744 567 890',
 'We sell organic produce to restaurants and hotels. Need a simple B2B ordering platform where our regular clients can place recurring orders, see availability, and track deliveries. M-Pesa and bank transfer payment.',
 'KES 400,000 - 700,000', '6-8 weeks',
 DATE_SUB(NOW(), INTERVAL 1 DAY), 'new'),

('Brian Kipchoge', 'brian@safarimarathon.co.ke', '+254 701 234 567',
 'Looking for a event management platform for our annual marathon. Need registration, payment processing, bib number generation, timing integration, results posting, and participant communication. About 5,000 participants annually.',
 'KES 600,000 - 900,000', '2-3 months',
 DATE_SUB(NOW(), INTERVAL 18 DAY), 'closed'),

('Nancy Akinyi', 'nancy@eastlandspharmacy.co.ke', '+254 712 876 543',
 'Our pharmacy chain has 4 locations and we need an inventory management system that tracks stock levels, expiry dates, and automatically generates re-order alerts. Need integration with our POS system.',
 'KES 500,000 - 800,000', '2 months',
 DATE_SUB(NOW(), INTERVAL 15 DAY), 'in_discussion'),

('Kevin Mutua', 'kevin@constructionke.co.ke', '+254 723 987 654',
 'We are a construction company and need project management software. Track projects by phase, manage subcontractors, handle material procurement, budget tracking, and generate progress reports for clients.',
 'KES 900,000 - 1,400,000', '3-4 months',
 DATE_SUB(NOW(), INTERVAL 20 DAY), 'closed');

-- Link services to quote requests (keyed by the submitter's name+email so
-- the mapping never depends on the quote_requests AUTO_INCREMENT counter)
INSERT INTO quote_request_services (quote_request_id, service_id, service_title) VALUES
((SELECT id FROM quote_requests WHERE name = 'Grace Wanjiku' AND email = 'grace.wanjiku@savannatech.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'E-commerce website development' LIMIT 1), 'E-commerce website development'),
((SELECT id FROM quote_requests WHERE name = 'Grace Wanjiku' AND email = 'grace.wanjiku@savannatech.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Website design & UI/UX development' LIMIT 1), 'Website design & UI/UX development'),
((SELECT id FROM quote_requests WHERE name = 'David Ochieng' AND email = 'david@uplandsbrewing.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Custom business software development' LIMIT 1), 'Custom business software development'),
((SELECT id FROM quote_requests WHERE name = 'David Ochieng' AND email = 'david@uplandsbrewing.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Inventory & asset management systems' LIMIT 1), 'Inventory & asset management systems'),
((SELECT id FROM quote_requests WHERE name = 'Amina Hassan' AND email = 'amina@swahilitechstartup.com' LIMIT 1), (SELECT id FROM services WHERE title = 'Cross-platform app development' LIMIT 1), 'Cross-platform app development'),
((SELECT id FROM quote_requests WHERE name = 'Amina Hassan' AND email = 'amina@swahilitechstartup.com' LIMIT 1), (SELECT id FROM services WHERE title = 'Backend & API development' LIMIT 1), 'Backend & API development'),
((SELECT id FROM quote_requests WHERE name = 'James Kamau' AND email = 'james.kamau@highlandfarmltd.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'ERP implementation & configuration' LIMIT 1), 'ERP implementation & configuration'),
((SELECT id FROM quote_requests WHERE name = 'James Kamau' AND email = 'james.kamau@highlandfarmltd.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Custom business software development' LIMIT 1), 'Custom business software development'),
((SELECT id FROM quote_requests WHERE name = 'Fatima Ali' AND email = 'fatima@safaridiscounts.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Logo & visual identity design' LIMIT 1), 'Logo & visual identity design'),
((SELECT id FROM quote_requests WHERE name = 'Fatima Ali' AND email = 'fatima@safaridiscounts.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Website redesign & modernization' LIMIT 1), 'Website redesign & modernization'),
((SELECT id FROM quote_requests WHERE name = 'Peter Njoroge' AND email = 'peter@njoroge-law.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Custom business software development' LIMIT 1), 'Custom business software development'),
((SELECT id FROM quote_requests WHERE name = 'Peter Njoroge' AND email = 'peter@njoroge-law.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Mobile app UI/UX design' LIMIT 1), 'Mobile app UI/UX design'),
((SELECT id FROM quote_requests WHERE name = 'Sarah Chebet' AND email = 'sarah@tuitionhub.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Cross-platform app development' LIMIT 1), 'Cross-platform app development'),
((SELECT id FROM quote_requests WHERE name = 'Sarah Chebet' AND email = 'sarah@tuitionhub.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Backend & API development' LIMIT 1), 'Backend & API development'),
((SELECT id FROM quote_requests WHERE name = 'Sarah Chebet' AND email = 'sarah@tuitionhub.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Database design & architecture' LIMIT 1), 'Database design & architecture'),
((SELECT id FROM quote_requests WHERE name = 'Michael Otieno' AND email = 'michael@otssecurity.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Custom mobile app development (Android/iOS)' LIMIT 1), 'Custom mobile app development (Android/iOS)'),
((SELECT id FROM quote_requests WHERE name = 'Michael Otieno' AND email = 'michael@otssecurity.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Backend & API development' LIMIT 1), 'Backend & API development'),
((SELECT id FROM quote_requests WHERE name = 'Lucy Wambui' AND email = 'lucy@greenacres.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'E-commerce website development' LIMIT 1), 'E-commerce website development'),
((SELECT id FROM quote_requests WHERE name = 'Lucy Wambui' AND email = 'lucy@greenacres.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Progressive web apps (PWA)' LIMIT 1), 'Progressive web apps (PWA)'),
((SELECT id FROM quote_requests WHERE name = 'Brian Kipchoge' AND email = 'brian@safarimarathon.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Custom business software development' LIMIT 1), 'Custom business software development'),
((SELECT id FROM quote_requests WHERE name = 'Brian Kipchoge' AND email = 'brian@safarimarathon.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Website design & UI/UX development' LIMIT 1), 'Website design & UI/UX development'),
((SELECT id FROM quote_requests WHERE name = 'Nancy Akinyi' AND email = 'nancy@eastlandspharmacy.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Inventory & asset management systems' LIMIT 1), 'Inventory & asset management systems'),
((SELECT id FROM quote_requests WHERE name = 'Nancy Akinyi' AND email = 'nancy@eastlandspharmacy.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Systems integration' LIMIT 1), 'Systems integration'),
((SELECT id FROM quote_requests WHERE name = 'Kevin Mutua' AND email = 'kevin@constructionke.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'Custom business software development' LIMIT 1), 'Custom business software development'),
((SELECT id FROM quote_requests WHERE name = 'Kevin Mutua' AND email = 'kevin@constructionke.co.ke' LIMIT 1), (SELECT id FROM services WHERE title = 'HR & payroll system development' LIMIT 1), 'HR & payroll system development');

-- =====================================================================
-- Contact Messages (10 realistic inquiries)
-- =====================================================================
INSERT INTO contact_messages (name, email, subject, message, submitted_at, status) VALUES
('John Maina', 'john.maina@gmail.com', 'Question about your services',
 'Hi, I am starting a small restaurant in Westlands and need a website. Do you also handle food delivery app integration? I saw competitors like Glovo and want something similar but for my own riders.',
 DATE_SUB(NOW(), INTERVAL 14 DAY), 'replied'),

('Esther Wairimu', 'esther@wairimuenterprises.co.ke', 'Partnership inquiry',
 'We are an accounting firm and I think our clients could benefit from your ERP services. Would you be open to a referral partnership? We handle their books and often get asked about system implementation.',
 DATE_SUB(NOW(), INTERVAL 11 DAY), 'read'),

('Daniel Kiprotich', 'daniel.kiprotich@yahoo.com', 'Pricing for mobile app',
 'Hello, I have an idea for a dating app for Kenyan professionals. Can you give me a rough estimate of how much it would cost to build? Just the MVP with user profiles, matching, and chat. Android first.',
 DATE_SUB(NOW(), INTERVAL 9 DAY), 'new'),

('Mary Njeri', 'mary@njerifashion.co.ke', 'Social media help needed',
 'Our fashion brand is growing but our social media presence is weak. We post sporadically and our Instagram does not look professional. Do you offer social media management or just the strategy part?',
 DATE_SUB(NOW(), INTERVAL 6 DAY), 'new'),

('Patrick Odhiambo', 'patrick@odhiamboassociates.com', 'SEO audit request',
 'We have a corporate law firm website that gets almost no organic traffic. We hired someone last year for SEO but nothing improved. Can you do an audit and tell us what is wrong? Happy to pay for the audit itself.',
 DATE_SUB(NOW(), INTERVAL 3 DAY), 'new'),

('Agnes Muthoni', 'agnes@thekitchen.co.ke', 'E-commerce redesign',
 'Our WooCommerce store is slow and keeps breaking. We sell artisanal foods and have about 200 products. Would it be better to rebuild from scratch or try to fix what we have? Need advice on the right approach.',
 DATE_SUB(NOW(), INTERVAL 2 DAY), 'read'),

('Samuel Kipkorir', 'samuel@gridsafaris.co.ke', 'Thank you and review',
 'Just wanted to say thanks for the work you did on our booking platform. It has been running smoothly for 6 months now and our clients love it. Will definitely recommend you to others in the tourism sector.',
 DATE_SUB(NOW(), INTERVAL 16 DAY), 'replied'),

('Rose Adhiambo', 'rose@techwomenke.org', 'Free consultation?',
 'We are a non-profit that helps women get into tech. We need a simple website to showcase our programs and accept applications. Our budget is very limited as a non-profit. Do you offer any discounts for NGOs?',
 DATE_SUB(NOW(), INTERVAL 5 DAY), 'new'),

('Hassan Mohammed', 'hassan@coastlogistics.co.ke', 'Integration question',
 'We use QuickBooks for accounting and a separate warehouse management system. Need them to talk to each other — currently manually exporting and importing CSV files daily. Is this something you handle?',
 DATE_SUB(NOW(), INTERVAL 1 DAY), 'new'),

('Janet Wakesho', 'janet@wakeshoconsultants.co.ke', 'IT infrastructure advice',
 'We are moving our office and need advice on the new IT setup — server room, network design, WiFi coverage, security cameras, and VPN for remote workers. Do you consult on physical infrastructure or just software?',
 DATE_SUB(NOW(), INTERVAL 8 DAY), 'read');

-- =====================================================================
-- Reviews (14 total: 3 pending, 8 approved, 3 rejected)
-- =====================================================================

-- Pending reviews (3)
INSERT INTO reviews (name, company, rating, review_text, status, submitted_at) VALUES
('James Kariuki', 'Savanna Tech Solutions', 5,
 'Brightframe built our company website and the result exceeded our expectations. Jairus took the time to understand our business before writing any code. The site loads fast, looks professional, and our inquiries doubled within the first month.',
 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),

('Faith Nekesa', 'Green Valley Farms', 4,
 'Working with Brightframe on our farm management system was a good experience. They delivered on time and the system works well. Communication was clear throughout the process. Minor delays on the reporting module but overall satisfied.',
 'pending', DATE_SUB(NOW(), INTERVAL 3 DAY)),

('Alex Omondi', 'Nairobi Digital Agency', 5,
 'Hired Brightframe for backend API development on a client project. Very solid work — clean code, well-documented endpoints, and they caught a security issue in our original spec that we had missed. Will work with them again.',
 'pending', DATE_SUB(NOW(), INTERVAL 5 DAY)),

-- Approved reviews (8)
('Daniel Wafula', 'Wafula & Associates', 5,
 'Our law firm needed a case management system and Brightframe delivered exactly what we described. The client portal alone has saved us hours of phone calls every week. Professional, responsive, and the system has been running without issues for 4 months now.',
 'approved', DATE_SUB(NOW(), INTERVAL 45 DAY)),

('Maria Gonzalez', 'East Africa Exports Ltd', 5,
 'We needed an inventory system for our export business with multi-currency support. Brightframe built it from scratch and it handles everything — Shillings, Dollars, Euros — with proper conversion tracking. Best investment we made last year.',
 'approved', DATE_SUB(NOW(), INTERVAL 38 DAY)),

('Robert Kimani', 'TechHub Nairobi', 4,
 'Good experience working with Brightframe on our co-working space management platform. They understood the unique needs of a shared workspace — booking, billing, member management — and built something that actually fits how we operate.',
 'approved', DATE_SUB(NOW(), INTERVAL 30 DAY)),

('Aisha Osman', 'Coast Fresh Seafood', 5,
 'Our ordering system built by Brightframe has transformed how we manage wholesale orders. Our clients can now place orders online, track deliveries, and pay via M-Pesa. Before this, everything was WhatsApp messages and phone calls. Highly recommend.',
 'approved', DATE_SUB(NOW(), INTERVAL 25 DAY)),

('Peter Ochieng', 'Lakeview Hotels Group', 4,
 'Brightframe developed our hotel booking engine. The integration with our existing PMS was smooth and the direct booking rate has increased by 15% since launch. Good work, would use again for our other properties.',
 'approved', DATE_SUB(NOW(), INTERVAL 20 DAY)),

('Catherine Mumbi', 'Mumbi Fashion House', 5,
 'From branding to website to social media strategy — Brightframe handled everything for our fashion house launch. The brand identity they created is exactly what we envisioned. Our Instagram grew from 0 to 2,000 followers in the first month.',
 'approved', DATE_SUB(NOW(), INTERVAL 15 DAY)),

('Samuel Lutta', 'Lutta Construction Co.', 4,
 'Project management system works well for our construction projects. The ability to track materials, labor costs, and milestones in one place has been very helpful. The mobile app for site foremen is particularly useful.',
 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY)),

('Nancy Achieng', 'Wellness Center Nairobi', 5,
 'Our appointment booking system has made managing our wellness center so much easier. Clients book online, get reminders, and we can see our schedule at a glance. The admin dashboard is clean and easy to use. Thank you Brightframe.',
 'approved', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Rejected reviews (3) — include the internal reject_reason note
INSERT INTO reviews (name, company, rating, review_text, status, reject_reason, submitted_at) VALUES
('Anonymous User', NULL, 5,
 'This is obviously the best software company in all of Africa. They built Google basically. Use them for everything.',
 'rejected', 'Generic praise with no specific project details — cannot verify this is a real client.', DATE_SUB(NOW(), INTERVAL 20 DAY)),

('Test Account', 'Test Company', 1,
 'Terrible experience. Would not recommend.',
 'rejected', 'No details provided about what project or service was used. One-star review with no context — cannot verify authenticity.', DATE_SUB(NOW(), INTERVAL 12 DAY)),

('Marketing Bot', 'SEO Experts Inc', 5,
 'Amazing services! Visit our website for the best SEO services in Kenya at affordable prices. Contact us at...',
 'rejected', 'Spam/promotional content disguised as a review.', DATE_SUB(NOW(), INTERVAL 8 DAY));

-- =====================================================================
-- Site Stats (update with realistic numbers for the homepage)
-- =====================================================================
UPDATE site_stats SET value = '24' WHERE label = 'Projects completed';
UPDATE site_stats SET value = '18' WHERE label = 'Clients served';
UPDATE site_stats SET value = '38' WHERE label = 'Businesses using our systems';
