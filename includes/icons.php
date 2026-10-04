<?php
/**
 * Category-slug-keyed lookups shared across pages: icon SVGs (used both
 * per-service, via icon_name, and per-category) and short one-line
 * blurbs (used wherever a category needs summarizing rather than fully
 * listing its services — the homepage overview, the services page).
 */
function render_service_icon(string $iconName): string
{
    $icons = [
        'web-development-design' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 9h18"/><path d="m9.5 13-2 2 2 2m5-4 2 2-2 2"/></svg>',
        'mobile-development' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
        'software-systems-development' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
        'erp-business-systems' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="8" height="8" rx="1"/><rect x="13" y="3" width="8" height="8" rx="1"/><rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/></svg>',
        'branding' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>',
        'digital-marketing' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9v6h4l6 4V5L7 9H3Z"/><path d="M16 9a4 4 0 0 1 0 6"/><path d="M19.5 6a8 8 0 0 1 0 12"/></svg>',
        'seo' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>',
        'it-technical-consulting' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="m15.5 8.5-1.8 5.2-5.2 1.8 1.8-5.2 5.2-1.8Z"/></svg>',
    ];

    // Generic fallback icon for any icon_name not in the map above.
    $fallback = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>';

    return $icons[$iconName] ?? $fallback;
}

/**
 * Short one-line description for a service category, keyed by its slug.
 */
function category_blurb(string $slug): string
{
    $blurbs = [
        'web-development-design'       => 'Full-stack web apps, e-commerce, and websites built to perform, not just look good.',
        'mobile-development'           => 'Native and cross-platform apps built for Android and iOS.',
        'software-systems-development' => 'Custom software, APIs, and databases built around how your business runs.',
        'erp-business-systems'         => 'ERP, CRM, HR, and automation systems configured around your real workflows.',
        'branding'                     => 'Visual identity and brand strategy that makes you recognizable.',
        'digital-marketing'            => 'Social, content, email, and paid campaigns that bring in leads.',
        'seo'                          => 'Technical and content SEO that gets you found by the right searches.',
        'it-technical-consulting'      => 'An outside, technical view on what to build, fix, or modernize next.',
    ];

    return $blurbs[$slug] ?? '';
}

/**
 * Hero intro for a category's dedicated detail page (services/<slug>.php)
 * — 2-3 sentences, one level more detail than category_blurb(). The
 * longer "why this matters" copy (a paragraph or two deeper) lives in
 * category_why_matters() in includes/category-content.php.
 */
function category_intro(string $slug): string
{
    $intros = [
        'web-development-design'       => "From your first website to a fully custom web application, we build for real use — fast to load, easy to maintain, and built around how your users actually behave online. Every project is built as one connected system: the interface, the backend, and the database behind it, not separate pieces stitched together.",
        'mobile-development'           => "Native or cross-platform, Android or iOS — we build mobile apps that feel fast, work reliably, and stay easy to update as your product grows. Every app starts from how people will actually use it day to day, not a generic template adapted after the fact.",
        'software-systems-development' => "Custom software, APIs, and the databases behind them — built around how your business actually operates, not a generic template. Where an off-the-shelf tool almost fits but not quite, custom software closes that gap without forcing your team to change how they work around the software's limitations.",
        'erp-business-systems'         => "ERP, CRM, HR, and automation systems configured around your real workflows — so your team spends less time on manual, repetitive work. These are the systems that run the operational core of a business, which means getting the configuration right matters more here than almost anywhere else.",
        'branding'                     => "A distinct visual identity, from logo to guidelines — built to make your business recognizable and consistent everywhere it shows up. Strong branding isn't decoration; it's what lets people recognize and trust you before they've read a word.",
        'digital-marketing'            => "Social, content, email, and paid campaigns built to bring in leads — planned around your goals and budget, not a one-size-fits-all package. Marketing only earns its cost when it's aimed at a clear, measurable outcome, not just activity for its own sake.",
        'seo'                          => "Technical and content SEO built to get you found by the searches that matter — from on-page fixes to a content strategy built around what your customers actually search. SEO is a long-term investment: the goal is being reliably findable, not a short-lived ranking spike.",
        'it-technical-consulting'      => "An outside, technical view on what to build, fix, or modernize next — from infrastructure to system architecture, before you commit budget to it. Sometimes the most valuable deliverable isn't more code, it's an honest second opinion before a decision gets expensive to reverse.",
    ];

    return $intros[$slug] ?? category_blurb($slug);
}

/**
 * Read-only star rating display (1-5 filled stars) for an approved
 * review. Used on reviews.php.
 */
function render_star_rating(int $rating): string
{
    $rating = max(1, min(5, $rating));
    $filled = '<svg width="16" height="16" viewBox="0 0 24 24" fill="#FFD166" stroke="#FFD166" stroke-width="1.5" aria-hidden="true"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>';
    $empty  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D3DDD8" stroke-width="1.5" aria-hidden="true"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>';

    return str_repeat($filled, $rating) . str_repeat($empty, 5 - $rating);
}

/**
 * Where a category's "learn more" link should point: its dedicated
 * services/<slug>.php page if one actually exists as a file, otherwise
 * falling back to its section on the main services.php catalog. Needed
 * because admin/services.php can create new categories in the database,
 * but a dedicated detail page is a separate static file that doesn't get
 * generated automatically (see the note at the top of admin/services.php)
 * — without this fallback, a category added there would produce dead
 * /services/<slug>.php links in the nav, footer, and homepage until a
 * developer added that file by hand.
 */
function category_detail_url(string $slug, string $basePath = ''): string
{
    // Defensive: only ever treat a slug matching this shape as a possible
    // filename, regardless of where it came from — slugify() already
    // guarantees this for anything created through admin/services.php,
    // but this function shouldn't trust that if the value came from
    // somewhere else (e.g. a direct database edit).
    if (preg_match('/^[a-z0-9-]+$/', $slug) && file_exists(__DIR__ . '/../services/' . $slug . '.php')) {
        return $basePath . 'services/' . $slug . '.php';
    }
    return $basePath . 'services.php#cat-' . $slug;
}
