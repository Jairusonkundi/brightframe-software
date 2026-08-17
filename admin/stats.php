<?php
/**
 * Admin: edit the homepage stat tiles (site_stats table).
 *
 * `value` starts NULL/empty for every row (see database.sql) — deliberately
 * not seeded with numbers, since Brightframe doesn't have a track record
 * to report yet. includes/site-stats.php (the public display component)
 * only shows a tile once its value is filled in here, and hides the
 * whole section if every value is still empty.
 *
 * NOT PROTECTED — same as admin/submissions.php. Add authentication
 * before deploying this anywhere public.
 */
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids    = $_POST['id'] ?? [];
    $labels = $_POST['label'] ?? [];
    $values = $_POST['value'] ?? [];

    try {
        $stmt = $pdo->prepare('UPDATE site_stats SET label = :label, value = :value WHERE id = :id');
        foreach ($ids as $i => $id) {
            $label = trim($labels[$i] ?? '');
            $value = trim($values[$i] ?? '');
            if ($label === '') {
                continue; // never save a blank label — a stat with no label can't display sensibly
            }
            $stmt->execute([
                'id'    => (int) $id,
                'label' => $label,
                'value' => $value !== '' ? $value : null,
            ]);
        }
    } catch (PDOException $e) {
        error_log('Failed to update site stats: ' . $e->getMessage());
    }

    header('Location: stats.php?saved=1');
    exit;
}

try {
    $stats = $pdo->query('SELECT id, label, value, display_order FROM site_stats ORDER BY display_order ASC')->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load site stats: ' . $e->getMessage());
    $stats = [];
}

$pageTitle = 'Site stats — Brightframe Software Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/styles.css?v=<?= @filemtime(__DIR__ . '/../css/styles.css') ?: '1' ?>">
</head>
<body>
<div class="admin-wrap">
  <h1>Site stats</h1>
  <p class="admin-note">
    <strong>Unprotected page.</strong> This view has no login or access control.
    Add authentication before deploying it anywhere outside local development.
  </p>

  <nav class="admin-quicknav" aria-label="Jump to section">
    <a href="submissions.php">Submissions</a>
    <a href="reviews.php">Reviews</a>
  </nav>

  <section class="admin-section">
    <h2>Homepage stat tiles</h2>
    <p class="admin-section-note">
      Leave a value blank to keep that tile hidden. The stats section on the homepage only
      appears once at least one tile below has a value — nothing displays until you fill one in.
    </p>

    <?php if (isset($_GET['saved'])): ?>
      <p class="admin-saved-note">Saved.</p>
    <?php endif; ?>

    <?php if (empty($stats)): ?>
      <div class="admin-empty">
        <p class="admin-empty-title">No stat tiles configured</p>
        <p class="admin-empty-note">Add rows to the <code>site_stats</code> table to get started.</p>
      </div>
    <?php else: ?>
      <form method="post" class="admin-stats-form">
        <?php foreach ($stats as $i => $stat): ?>
          <div class="admin-stats-row">
            <input type="hidden" name="id[]" value="<?= (int) $stat['id'] ?>">
            <div class="admin-field">
              <label for="label-<?= (int) $stat['id'] ?>">Label</label>
              <input id="label-<?= (int) $stat['id'] ?>" name="label[]" type="text" value="<?= htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="admin-field">
              <label for="value-<?= (int) $stat['id'] ?>">Value <span class="admin-field-hint">blank = hidden</span></label>
              <input id="value-<?= (int) $stat['id'] ?>" name="value[]" type="text" placeholder="e.g. 12" value="<?= htmlspecialchars($stat['value'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="admin-btn admin-btn-primary admin-stats-submit">Save stats</button>
      </form>
    <?php endif; ?>
  </section>
</div>
</body>
</html>
