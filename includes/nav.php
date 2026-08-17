<?php
/**
 * Services dropdown is DB-driven: edit the `service_categories` /
 * `services` tables to change what shows here — no HTML changes needed.
 */
require_once __DIR__ . '/../config/db.php';

try {
    $navRows = $pdo->query(
        'SELECT sc.name AS category_name, sc.display_order AS cat_order,
                s.title, s.display_order AS svc_order
         FROM service_categories sc
         LEFT JOIN services s ON s.category_id = sc.id
         ORDER BY sc.display_order ASC, s.display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load nav service categories: ' . $e->getMessage());
    $navRows = [];
}

$navServiceCategories = [];
foreach ($navRows as $row) {
    $cat = $row['category_name'];
    if (!isset($navServiceCategories[$cat])) {
        $navServiceCategories[$cat] = [];
    }
    if ($row['title'] !== null) {
        $navServiceCategories[$cat][] = $row['title'];
    }
}
?>
<nav>
  <div class="wrap navbar-inner">
    <div class="logo">
      <svg width="30" height="30" viewBox="0 0 30 30" role="img" aria-label="Brightframe Software logo">
        <rect x="4" y="4" width="22" height="22" rx="6" fill="none" stroke="#0B0E1A" stroke-width="2.2"/>
        <path d="M10 15 L13.5 18.5 L20 11" stroke="#5B5FEF" stroke-width="2.4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      Brightframe Software
    </div>

    <button type="button" class="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Toggle menu">
      <span></span><span></span><span></span>
    </button>

    <div class="navlinks" id="primary-nav">
      <a href="#" class="nav-link">Home</a>

      <div class="nav-dropdown">
        <button type="button" class="nav-link dropdown-toggle" aria-expanded="false">
          Services
          <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="dropdown-panel dropdown-mega">
          <?php if (empty($navServiceCategories)): ?>
            <p class="dropdown-note">Services are temporarily unavailable.</p>
          <?php else: ?>
            <?php foreach ($navServiceCategories as $categoryName => $serviceTitles): ?>
              <div class="dropdown-col">
                <div class="dropdown-col-head"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></div>
                <?php foreach ($serviceTitles as $title): ?>
                  <a href="#services"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
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
        <button type="button" class="nav-link dropdown-toggle" aria-expanded="false">
          Company
          <svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
        </button>
        <div class="dropdown-panel dropdown-1col">
          <a href="#about">About Us</a>
          <a href="#team">Our Team</a>
          <a href="#why-us">Why Choose Us</a>
        </div>
      </div>

      <a href="#contact" class="nav-link">Contact Us</a>

      <!-- Mobile-only: primary CTA lives inside the collapsed menu too -->
      <div class="nav-actions-mobile">
        <a href="#contact" class="nav-cta">Request a quote</a>
      </div>
    </div>

    <div class="nav-actions">
      <a href="#contact" class="nav-cta">Request a quote</a>
    </div>
  </div>
</nav>
