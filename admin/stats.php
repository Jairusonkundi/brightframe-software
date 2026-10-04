<?php
/**
 * Admin: Site Stats — two distinct things on one page, clearly separated:
 *
 * 1. Analytics (read-only) — real counts pulled straight from the
 *    database: total/status-broken-down submissions, reviews by status,
 *    services count, and a simple submissions-per-week chart. This is
 *    just for the admin's own visibility.
 * 2. Homepage stat tiles (editable) — what's shown on the public
 *    homepage's numbers band. `value` starts empty for every row (see
 *    database.sql) — deliberately not seeded with numbers, since
 *    Brightframe doesn't have a track record to report yet.
 *    includes/site-stats.php only shows a tile once its value is filled
 *    in here, and hides the whole section if every value is still empty.
 *    These two things are NOT the same — the analytics numbers below are
 *    real and automatic; the homepage tiles are manual and public, and
 *    it's entirely possible (likely, even, at first) for the analytics
 *    to show real activity while the public tiles stay empty because
 *    there's nothing worth publishing yet.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
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

    $totalQuotes   = (int) $pdo->query('SELECT COUNT(*) FROM quote_requests')->fetchColumn();
    $totalMessages = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $totalCategories = (int) $pdo->query('SELECT COUNT(*) FROM service_categories')->fetchColumn();
    $totalServices  = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();

    $reviewCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    $reviewRows = $pdo->query("SELECT status, COUNT(*) AS c FROM reviews GROUP BY status")->fetchAll();
    foreach ($reviewRows as $row) {
        $reviewCounts[$row['status']] = (int) $row['c'];
    }

    // Submissions per week, last 8 weeks — quotes and messages combined,
    // counted by ISO week. A small enough range that one query per table
    // is simpler than a UNION across differently-shaped tables.
    $weeklyQuotes = $pdo->query(
        "SELECT YEARWEEK(submitted_at, 3) AS yw, COUNT(*) AS c FROM quote_requests
         WHERE submitted_at >= (NOW() - INTERVAL 8 WEEK) GROUP BY yw"
    )->fetchAll(PDO::FETCH_KEY_PAIR);
    $weeklyMessages = $pdo->query(
        "SELECT YEARWEEK(submitted_at, 3) AS yw, COUNT(*) AS c FROM contact_messages
         WHERE submitted_at >= (NOW() - INTERVAL 8 WEEK) GROUP BY yw"
    )->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    error_log('Failed to load site stats analytics: ' . $e->getMessage());
    $stats = [];
    $totalQuotes = $totalMessages = $totalCategories = $totalServices = 0;
    $reviewCounts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    $weeklyQuotes = $weeklyMessages = [];
}

// Build the last 8 week buckets (oldest first) even if some have zero
// submissions, so the chart always shows a continuous 8-week span.
$weeklyBuckets = [];
for ($i = 7; $i >= 0; $i--) {
    $ts = strtotime("-{$i} weeks");
    $yw = (int) date('oW', $ts); // ISO year+week, matches YEARWEEK(..., 3)
    $weeklyBuckets[] = [
        'label' => date('M j', strtotime('monday this week', $ts)),
        'count' => (int) ($weeklyQuotes[$yw] ?? 0) + (int) ($weeklyMessages[$yw] ?? 0),
    ];
}
$weeklyMax = max(1, max(array_column($weeklyBuckets, 'count')));

$pageTitle   = 'Site Stats';
$adminActive = 'stats';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Site Stats</h1>
    <p class="admin-page-sub">Real analytics from your data, plus the numbers shown publicly on the homepage — two separate things, see below.</p>
  </div>
</div>

<div class="admin-kpi-grid">
  <div class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Quote requests</span>
      <span class="admin-kpi-ico kpi-indigo"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></span>
    </div>
    <div class="admin-kpi-value"><?= $totalQuotes ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Contact messages</span>
      <span class="admin-kpi-ico kpi-cyan"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg></span>
    </div>
    <div class="admin-kpi-value"><?= $totalMessages ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Reviews approved</span>
      <span class="admin-kpi-ico kpi-green"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg></span>
    </div>
    <div class="admin-kpi-value"><?= $reviewCounts['approved'] ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Reviews pending</span>
      <span class="admin-kpi-ico kpi-amber"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
    </div>
    <div class="admin-kpi-value"><?= $reviewCounts['pending'] ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Service categories</span>
      <span class="admin-kpi-ico kpi-violet"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 3v18M3 12h18"/></svg></span>
    </div>
    <div class="admin-kpi-value"><?= $totalCategories ?></div>
  </div>
  <div class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Services listed</span>
      <span class="admin-kpi-ico kpi-rose"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m8 6 4-4 4 4"/><path d="M12 2v13"/><path d="M4 22h16"/></svg></span>
    </div>
    <div class="admin-kpi-value"><?= $totalServices ?></div>
  </div>
</div>

<div class="admin-panel" style="margin-bottom: 28px;">
  <h2>Submissions, last 8 weeks</h2>
  <p class="admin-panel-note">Quote requests + contact messages combined, by week.</p>
  <div class="admin-bar-chart">
    <?php foreach ($weeklyBuckets as $bucket): ?>
      <div class="admin-bar-col">
        <div class="admin-bar" style="height: <?= max(4, round($bucket['count'] / $weeklyMax * 100)) ?>%;" title="<?= $bucket['count'] ?> submissions"></div>
        <div class="admin-bar-value"><?= $bucket['count'] ?></div>
        <div class="admin-bar-label"><?= htmlspecialchars($bucket['label'], ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<hr class="admin-section-divider">

<div class="admin-panel">
  <h2>Homepage stat tiles</h2>
  <p class="admin-panel-note">
    What's shown publicly on the homepage's numbers band — separate from the analytics above.
    Leave a value blank to keep that tile hidden; the section only appears once at least one tile has a value.
  </p>

  <?php if (isset($_GET['saved'])): ?>
    <p class="admin-saved-note">Saved.</p>
  <?php endif; ?>

  <?php if (empty($stats)): ?>
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
      </span>
      <p class="admin-empty-title">No stat tiles configured</p>
      <p class="admin-empty-note">Add rows to the <code>site_stats</code> table to get started.</p>
    </div>
  <?php else: ?>
    <form method="post" class="admin-stats-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
      <?php foreach ($stats as $stat): ?>
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
</div>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
