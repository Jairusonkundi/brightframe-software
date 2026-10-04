<?php
/**
 * Services dropdown is DB-driven: edit the `service_categories` /
 * `services` tables to change what shows here — no HTML changes needed.
 * Shows categories only (not all 38 individual services) — each links to
 * its section on services.php, which is where the full list lives.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/icons.php';

try {
    $navCategories = $pdo->query(
        'SELECT sc.name, sc.slug, COUNT(s.id) AS svc_count
         FROM service_categories sc
         LEFT JOIN services s ON s.category_id = sc.id
         GROUP BY sc.id, sc.name, sc.slug, sc.display_order
         ORDER BY sc.display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load nav service categories: ' . $e->getMessage());
    $navCategories = [];
}

// Set by pages one level deep (services/<slug>.php) before including
// header.php (which includes this file) — defaults to root-relative.
$basePath = $basePath ?? '';

// Which nav item to mark current, based on the requested script — so it
// stays highlighted for as long as you're on that page, not just on hover.
$navCurrentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$navIsHome      = $navCurrentPage === 'index.php';
// $basePath === '../' is only ever set by services/<slug>.php pages, so it
// doubles as "we're on a category detail page" without needing a second flag.
$navIsServices  = $navCurrentPage === 'services.php' || $basePath === '../';
$navIsCompany   = in_array($navCurrentPage, ['about.php', 'why-us.php', 'reviews.php'], true);
$navIsContact   = $navCurrentPage === 'contact.php';
?>
<nav>
  <div class="wrap navbar-inner">
    <a href="<?= $basePath ?>index.php" class="logo" aria-label="Brightframe Software — home">
      <svg width="30" height="30" viewBox="0 0 32 32" role="img" aria-hidden="true">
        <defs>
          <linearGradient id="logoSparkNav" x1="10" y1="10" x2="22" y2="22" gradientUnits="userSpaceOnUse">
            <stop offset="0" stop-color="#159F7A"/>
            <stop offset="1" stop-color="#5660EF"/>
          </linearGradient>
        </defs>
        <rect x="1" y="1" width="30" height="30" rx="9" fill="#EEF6F2"/>
        <path d="M6 13 L6 7 L13 7" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19 7 L26 7 L26 13" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M6 19 L6 25 L13 25" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M26 19 L26 25 L19 25" stroke="#134E4A" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M16 11 L17.3 14.7 L21 16 L17.3 17.3 L16 21 L14.7 17.3 L11 16 L14.7 14.7 Z" fill="url(#logoSparkNav)"/>
      </svg>
      Brightframe Software
    </a>

    <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <div class="navlinks" id="primary-nav">
      <a href="<?= $basePath ?>index.php" class="nav-link<?= $navIsHome ? ' is-current' : '' ?>">Home</a>

      <div class="nav-dropdown">
        <button type="button" class="nav-link dropdown-toggle<?= $navIsServices ? ' is-current' : '' ?>" aria-expanded="false">
          Services
          <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="dropdown-panel dropdown-mega dropdown-mega-cats">
          <?php if (empty($navCategories)): ?>
            <p class="dropdown-note">Services are temporarily unavailable.</p>
          <?php else: ?>
            <?php foreach ($navCategories as $cat): ?>
              <?php $catIsCurrent = $navCurrentPage === $cat['slug'] . '.php'; ?>
              <a class="dropdown-cat-card<?= $catIsCurrent ? ' is-current' : '' ?>" href="<?= htmlspecialchars(category_detail_url($cat['slug'], $basePath), ENT_QUOTES, 'UTF-8') ?>">
                <span class="dropdown-cat-ico"><?= render_service_icon($cat['slug']) ?></span>
                <span class="dropdown-cat-text">
                  <span class="dropdown-cat-name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
                  <span class="dropdown-cat-count"><?= (int) $cat['svc_count'] ?> service<?= (int) $cat['svc_count'] === 1 ? '' : 's' ?></span>
                </span>
              </a>
            <?php endforeach; ?>
            <a class="dropdown-cat-viewall" href="<?= $basePath ?>services.php">
              View all services
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <div class="nav-dropdown">
        <button type="button" class="nav-link dropdown-toggle" aria-expanded="false">
          Products
          <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="dropdown-panel dropdown-1col">
          <p class="dropdown-note">We're currently building our first product &mdash; check back soon.</p>
        </div>
      </div>

      <div class="nav-dropdown">
        <button type="button" class="nav-link dropdown-toggle<?= $navIsCompany ? ' is-current' : '' ?>" aria-expanded="false">
          Company
          <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="dropdown-panel dropdown-1col">
          <a href="<?= $basePath ?>about.php"<?= $navCurrentPage === 'about.php' ? ' class="is-current"' : '' ?>>About Us</a>
          <a href="<?= $basePath ?>about.php#team"<?= $navCurrentPage === 'about.php' ? ' class="is-current"' : '' ?>>Our Team</a>
          <a href="<?= $basePath ?>why-us.php"<?= $navCurrentPage === 'why-us.php' ? ' class="is-current"' : '' ?>>Why Choose Us</a>
          <a href="<?= $basePath ?>reviews.php"<?= $navCurrentPage === 'reviews.php' ? ' class="is-current"' : '' ?>>Reviews</a>
        </div>
      </div>

      <a href="<?= $basePath ?>contact.php" class="nav-link<?= $navIsContact ? ' is-current' : '' ?>">Contact Us</a>

      <!-- Mobile-only: primary CTA + theme toggle live inside the collapsed menu too -->
      <div class="nav-actions-mobile">
        <a href="<?= $basePath ?>quote.php" class="nav-cta">Request a quote</a>
        <button type="button" class="theme-toggle" aria-label="Switch to dark mode" title="Switch to dark mode">
          <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
          <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
        </button>
      </div>
    </div>

    <div class="nav-actions">
      <button type="button" class="theme-toggle" aria-label="Switch to dark mode" title="Switch to dark mode">
        <svg class="icon-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
        <svg class="icon-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
      </button>
      <a href="<?= $basePath ?>quote.php" class="nav-cta">Request a quote</a>
    </div>
  </div>
</nav>
