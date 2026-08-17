<?php
/**
 * Homepage stat tiles (e.g. "Projects completed"). Admin-editable via
 * admin/stats.php. Deliberately renders nothing at all if every stat's
 * value is still empty — see database.sql's note on the `site_stats`
 * table for why nothing is seeded with numbers by default.
 */
require_once __DIR__ . '/../config/db.php';

try {
    $siteStats = $pdo->query(
        "SELECT label, value FROM site_stats WHERE value IS NOT NULL AND value != '' ORDER BY display_order ASC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load site stats: ' . $e->getMessage());
    $siteStats = [];
}

if (!empty($siteStats)):
?>
<section class="stats-section reveal" aria-label="Brightframe Software in numbers">
  <div class="wrap">
    <div class="stats-grid">
      <?php foreach ($siteStats as $stat): ?>
        <div class="stat-tile">
          <div class="stat-value"><?= htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="stat-label"><?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
