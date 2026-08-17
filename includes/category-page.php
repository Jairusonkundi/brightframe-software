<?php
/**
 * Shared renderer for the dedicated per-category service pages
 * (services/<slug>.php). Each is a thin file that sets $categorySlug and
 * requires this — not meant to be requested directly itself.
 */
if (!isset($categorySlug)) {
    http_response_code(500);
    die('category-page.php requires $categorySlug to be set before including.');
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/icons.php';
require_once __DIR__ . '/illustrations.php';
require_once __DIR__ . '/category-content.php';

try {
    $catStmt = $pdo->prepare(
        'SELECT id, name, slug, display_order FROM service_categories WHERE slug = :slug LIMIT 1'
    );
    $catStmt->execute(['slug' => $categorySlug]);
    $category = $catStmt->fetch();

    $services = [];
    if ($category) {
        $svcStmt = $pdo->prepare(
            'SELECT id, title, description, long_description, icon_name FROM services
             WHERE category_id = :category_id ORDER BY display_order ASC'
        );
        $svcStmt->execute(['category_id' => $category['id']]);
        $services = $svcStmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log('Failed to load category page (' . $categorySlug . '): ' . $e->getMessage());
    $category = false;
    $services = [];
}

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Service category not found — Brightframe Software';
    $basePath  = '../';
    include __DIR__ . '/header.php';
    ?>
    <section class="section">
      <div class="wrap">
        <div class="legal-placeholder">
          <h1>Service category not found</h1>
          <p>That service category doesn't exist, or may have been renamed.</p>
          <a href="../services.php" class="btn btn-primary">Browse all services</a>
        </div>
      </div>
    </section>
    <?php
    include __DIR__ . '/footer.php';
    exit;
}

$pageTitle       = $category['name'] . ' — Brightframe Software';
$pageDescription = category_intro($category['slug']);

// Alternate which side the illustration sits on, category to category, so
// a run of single-category pages doesn't all look identical. Markup order
// is always [media, text] (media on the left by default); .media-row-reverse
// flips it to put the media on the right instead.
$imageOnRight = ((int) $category['display_order']) % 2 === 0;

$whyMatters      = category_why_matters($category['slug']);
$differentiators = category_differentiators($category['slug']);
$types           = category_types($category['slug']);

$basePath = '../';
include __DIR__ . '/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="media-row reveal<?= $imageOnRight ? ' media-row-reverse' : '' ?>">
      <div class="media-row-media">
        <?= render_category_illustration($category['slug']) ?>
      </div>
      <div class="media-row-text">
        <div class="sec-eyebrow">Service category</div>
        <h1><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p><?= htmlspecialchars(category_intro($category['slug']), ENT_QUOTES, 'UTF-8') ?></p>
        <div class="media-row-actions">
          <a href="../quote.php?category=<?= urlencode($category['slug']) ?>" class="btn btn-primary">Request a quote for this service</a>
          <a href="../services.php" class="btn btn-outline-dark">Browse all services</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if (!empty($whyMatters)): ?>
<section class="section section-alt">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Why this matters</div>
      <h2>What <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?> actually solves</h2>
    </div>
    <div class="category-copy reveal">
      <?php foreach ($whyMatters as $paragraph): ?>
        <p><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($differentiators)): ?>
<section class="section">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Why Brightframe</div>
      <h2>Why choose Brightframe Software for <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
    </div>
    <div class="whyus-grid">
      <?php foreach ($differentiators as $i => $point): ?>
        <div class="whyus-card reveal<?= $i > 0 ? ' reveal-delay-' . min($i, 3) : '' ?>">
          <div class="whyus-ico">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
          </div>
          <h4><?= htmlspecialchars($point['title'], ENT_QUOTES, 'UTF-8') ?></h4>
          <p><?= htmlspecialchars($point['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-alt">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">What's included</div>
      <h2><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?> services</h2>
      <p><?= count($services) ?> specific service<?= count($services) === 1 ? '' : 's' ?> under this category — pick exactly what you need when you request a quote.</p>
    </div>

    <?php if (empty($services)): ?>
      <p style="color:var(--ink-soft);">Services in this category are temporarily unavailable. Please check back shortly.</p>
    <?php else: ?>
      <div class="svc-detail-list reveal">
        <?php foreach ($services as $service): ?>
          <div class="svc-detail-row">
            <div class="svc-detail-ico"><?= render_service_icon($service['icon_name']) ?></div>
            <div>
              <h3><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></h3>
              <p><?= htmlspecialchars($service['long_description'] ?? $service['description'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!empty($types['items'])): ?>
<section class="section">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Good to know</div>
      <h2><?= htmlspecialchars($types['heading'], ENT_QUOTES, 'UTF-8') ?></h2>
      <?php if (!empty($types['intro'])): ?>
        <p><?= htmlspecialchars($types['intro'], ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
    </div>
    <div class="type-grid reveal">
      <?php foreach ($types['items'] as $item): ?>
        <div class="type-card">
          <h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3>
          <p><?= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section-alt">
  <div class="wrap">
    <div class="cta-band reveal">
      <div class="cta-content">
        <div class="eyebrow"><span class="dot"></span> Ready when you are</div>
        <h2>Get a quote for <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p>Tell us about your project and get a quote — we'll get back to you within a day with next steps.</p>
        <div class="cta-actions">
          <a href="../quote.php?category=<?= urlencode($category['slug']) ?>" class="btn btn-primary">Request a quote for this service</a>
          <a href="../contact.php" class="btn btn-outline">Have a question instead?</a>
        </div>
        <p class="cta-note">Takes less than 5 minutes — no obligation, no commitment.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>
