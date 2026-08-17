<?php
/**
 * Full services catalog — standalone page. Sticky category sidebar +
 * always-open sections (the sidebar is the navigation now, so the old
 * per-category <details> accordion no longer pulls its weight).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/icons.php';

$pageTitle       = 'Services — Brightframe Software';
$pageDescription = 'Web and mobile development, custom software, ERP and business systems, branding, digital marketing, SEO, and IT consulting — explore our full range of services.';

try {
    $categories = $pdo->query(
        'SELECT id, name, slug FROM service_categories ORDER BY display_order ASC'
    )->fetchAll();

    $allServices = $pdo->query(
        'SELECT id, category_id, title, description, icon_name
         FROM services ORDER BY display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load services: ' . $e->getMessage());
    $categories   = [];
    $allServices  = [];
}

$servicesByCategory = [];
foreach ($allServices as $svc) {
    $servicesByCategory[$svc['category_id']][] = $svc;
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">What we do</div>
      <h2>Services</h2>
      <p>From a first web app to full-scale systems, branding, and ongoing marketing — organized by category so you can jump straight to what you need.</p>
    </div>

    <?php if (empty($categories)): ?>
      <p style="color:var(--ink-soft);">Services are temporarily unavailable. Please check back shortly.</p>
    <?php else: ?>
      <div class="svc-layout">
        <nav class="svc-sidebar reveal" aria-label="Jump to service category">
          <?php foreach ($categories as $cat): ?>
            <?php $catServices = $servicesByCategory[$cat['id']] ?? []; ?>
            <?php if (empty($catServices)) continue; ?>
            <a class="svc-sidebar-link" href="#cat-<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>">
              <span class="svc-sidebar-ico"><?= render_service_icon($cat['slug']) ?></span>
              <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
              <span class="svc-sidebar-count"><?= count($catServices) ?></span>
            </a>
          <?php endforeach; ?>
        </nav>

        <div class="svc-content">
          <?php $catRevealDelays = ['', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3']; ?>
          <?php foreach ($categories as $catIndex => $cat): ?>
            <?php $catServices = $servicesByCategory[$cat['id']] ?? []; ?>
            <?php if (empty($catServices)) continue; ?>
            <?php $catDelayClass = $catRevealDelays[$catIndex % 4]; ?>
            <section class="svc-cat-block reveal <?= $catDelayClass ?>" id="cat-<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>">
              <div class="svc-cat-block-head">
                <div class="svc-cat-block-ico"><?= render_service_icon($cat['slug']) ?></div>
                <div>
                  <h2><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                  <p><?= htmlspecialchars(category_blurb($cat['slug']), ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="svc-cat-block-actions">
                  <span class="svc-cat-block-count"><?= count($catServices) ?> service<?= count($catServices) === 1 ? '' : 's' ?></span>
                  <a class="svc-cat-block-link" href="services/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>.php">
                    Full details
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
              </div>
              <div class="svc-cat-block-grid">
                <?php foreach ($catServices as $service): ?>
                  <div class="svc-card">
                    <div class="svc-card-ico"><?= render_service_icon($service['icon_name']) ?></div>
                    <div>
                      <h3><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                      <p><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="cta-band reveal">
      <div class="cta-content">
        <div class="eyebrow"><span class="dot"></span> Not sure where to start</div>
        <h2>Tell us what you're building</h2>
        <p>Pick the services you need on the request form, or just describe the project — we'll help you figure out the right scope.</p>
        <div class="cta-actions">
          <a href="quote.php" class="btn btn-primary">Request a quote</a>
          <a href="contact.php" class="btn btn-outline">Contact us</a>
        </div>
        <p class="cta-note">Takes less than 5 minutes — no obligation, no commitment.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
