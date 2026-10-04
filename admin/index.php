<?php
/**
 * Admin dashboard — the landing page after login. At-a-glance counts
 * (new quotes, new messages, pending reviews) plus a merged recent-
 * activity feed across all three submission types.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

try {
    $newQuoteCount    = (int) $pdo->query("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'")->fetchColumn();
    $newMessageCount  = (int) $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'")->fetchColumn();
    $pendingReviewCount = (int) $pdo->query("SELECT COUNT(*) FROM reviews WHERE status = 'pending'")->fetchColumn();
    $totalQuoteCount  = (int) $pdo->query('SELECT COUNT(*) FROM quote_requests')->fetchColumn();
    $totalMessageCount = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
} catch (PDOException $e) {
    error_log('Failed to load dashboard counts: ' . $e->getMessage());
    $newQuoteCount = $newMessageCount = $pendingReviewCount = $totalQuoteCount = $totalMessageCount = 0;
}

// Recent activity: pull the latest few rows from each of the 3 submission
// types, tag each with a type, merge, sort by date, and keep the newest N.
// Simpler and easier to reason about than a UNION query across tables
// with different column shapes.
$activity = [];
try {
    $recentQuotes = $pdo->query(
        'SELECT id, name, submitted_at FROM quote_requests ORDER BY submitted_at DESC LIMIT 10'
    )->fetchAll();
    foreach ($recentQuotes as $row) {
        $activity[] = [
            'type' => 'quote', 'time' => $row['submitted_at'],
            'text' => 'New quote request from ' . $row['name'],
            'href' => 'quote-view.php?id=' . (int) $row['id'],
        ];
    }

    $recentMessages = $pdo->query(
        'SELECT id, name, subject, submitted_at FROM contact_messages ORDER BY submitted_at DESC LIMIT 10'
    )->fetchAll();
    foreach ($recentMessages as $row) {
        $activity[] = [
            'type' => 'message', 'time' => $row['submitted_at'],
            'text' => 'New contact message from ' . $row['name'],
            'sub'  => $row['subject'],
            'href' => 'message-view.php?id=' . (int) $row['id'],
        ];
    }

    $recentReviews = $pdo->query(
        "SELECT id, name, status, submitted_at FROM reviews ORDER BY submitted_at DESC LIMIT 10"
    )->fetchAll();
    foreach ($recentReviews as $row) {
        $label = $row['status'] === 'pending' ? 'New review pending approval from ' : 'Review from ';
        $activity[] = [
            'type' => 'review', 'time' => $row['submitted_at'],
            'text' => $label . $row['name'],
            'href' => 'reviews.php',
        ];
    }
} catch (PDOException $e) {
    error_log('Failed to load dashboard activity: ' . $e->getMessage());
}

usort($activity, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));
$activity = array_slice($activity, 0, 8);

$pageTitle   = 'Dashboard';
$adminActive = 'dashboard';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Dashboard</h1>
    <p class="admin-page-sub">An overview of what's coming in and what needs your attention.</p>
  </div>
  <span class="admin-live-badge">
    <span class="admin-live-dot"></span>
    Live
  </span>
</div>

<div class="admin-kpi-grid">
  <a href="quotes.php?status=new" class="admin-kpi-card<?= $newQuoteCount > 0 ? ' is-attention' : '' ?>">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">New quote requests</span>
      <span class="admin-kpi-ico kpi-indigo">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h4"/></svg>
      </span>
    </div>
    <div class="admin-kpi-value"><?= $newQuoteCount ?></div>
  </a>
  <a href="messages.php?status=new" class="admin-kpi-card<?= $newMessageCount > 0 ? ' is-attention' : '' ?>">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">New contact messages</span>
      <span class="admin-kpi-ico kpi-cyan">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
      </span>
    </div>
    <div class="admin-kpi-value"><?= $newMessageCount ?></div>
  </a>
  <a href="reviews.php" class="admin-kpi-card<?= $pendingReviewCount > 0 ? ' is-attention' : '' ?>">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Pending reviews</span>
      <span class="admin-kpi-ico kpi-amber">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
      </span>
    </div>
    <div class="admin-kpi-value"><?= $pendingReviewCount ?></div>
  </a>
  <a href="quotes.php" class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Total quote requests</span>
      <span class="admin-kpi-ico kpi-green">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
      </span>
    </div>
    <div class="admin-kpi-value"><?= $totalQuoteCount ?></div>
  </a>
  <a href="messages.php" class="admin-kpi-card">
    <div class="admin-kpi-top">
      <span class="admin-kpi-label">Total contact messages</span>
      <span class="admin-kpi-ico kpi-violet">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/></svg>
      </span>
    </div>
    <div class="admin-kpi-value"><?= $totalMessageCount ?></div>
  </a>
</div>

<div class="admin-panel">
  <div class="admin-panel-head">
    <div>
      <h2>Recent activity</h2>
      <p class="admin-panel-note">The latest submissions across quote requests, contact messages, and reviews.</p>
    </div>
    <a href="quotes.php" class="admin-btn">View quote requests</a>
  </div>

  <?php if (empty($activity)): ?>
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
      </span>
      <p class="admin-empty-title">No activity yet</p>
      <p class="admin-empty-note">Quote requests, contact messages, and reviews will show up here as they come in.</p>
    </div>
  <?php else: ?>
    <div class="admin-activity-list">
      <?php foreach ($activity as $item): ?>
        <div class="admin-activity-item">
          <span class="admin-activity-ico type-<?= htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8') ?>">
            <?php if ($item['type'] === 'quote'): ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h4"/></svg>
            <?php elseif ($item['type'] === 'message'): ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
            <?php else: ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
            <?php endif; ?>
          </span>
          <div>
            <div class="admin-activity-text"><a href="<?= htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') ?></a></div>
            <?php if (!empty($item['sub'])): ?>
              <div class="admin-activity-sub"><?= htmlspecialchars($item['sub'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <div class="admin-activity-time"><?= htmlspecialchars(date('M j, Y g:ia', strtotime($item['time'])), ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
