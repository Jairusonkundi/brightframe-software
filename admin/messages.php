<?php
/**
 * Admin: contact messages list. Same pattern as quotes.php but for the
 * simpler contact_messages table (no services, budget, or timeline).
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

$validStatuses = ['new', 'read', 'replied'];
$statusFilter  = $_GET['status'] ?? '';
if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}
$sort = ($_GET['sort'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';

try {
    $sql = 'SELECT id, name, email, subject, message, submitted_at, status FROM contact_messages';
    $params = [];
    if ($statusFilter !== '') {
        $sql .= ' WHERE status = :status';
        $params['status'] = $statusFilter;
    }
    $sql .= ' ORDER BY submitted_at ' . ($sort === 'oldest' ? 'ASC' : 'DESC');
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $contactMessages = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load contact messages: ' . $e->getMessage());
    $contactMessages = [];
}

$statusLabels = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'];

$pageTitle   = 'Contact Messages';
$adminActive = 'messages';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Contact Messages</h1>
    <p class="admin-page-sub">General inquiries from the "Contact Us" form — questions, not project quotes.</p>
  </div>
</div>

<?php if (empty($contactMessages)): ?>
  <div class="admin-panel">
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
      </span>
      <p class="admin-empty-title"><?= $statusFilter !== '' ? 'No messages with this status' : 'No contact messages yet' ?></p>
      <p class="admin-empty-note">
        <?= $statusFilter !== '' ? 'Try a different status filter above.' : 'Messages from the "Contact Us" form will show up here as soon as someone sends one.' ?>
      </p>
    </div>
  </div>
<?php else: ?>
  <div class="admin-table-card">
    <form method="get" class="admin-filter-bar">
      <div class="admin-field">
        <label for="filter-status">Status</label>
        <select id="filter-status" name="status" onchange="this.form.submit()">
          <option value="">All statuses</option>
          <?php foreach ($statusLabels as $value => $label): ?>
            <option value="<?= $value ?>"<?= $statusFilter === $value ? ' selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="admin-field">
        <label for="filter-sort">Sort by date</label>
        <select id="filter-sort" name="sort" onchange="this.form.submit()">
          <option value="newest"<?= $sort === 'newest' ? ' selected' : '' ?>>Newest first</option>
          <option value="oldest"<?= $sort === 'oldest' ? ' selected' : '' ?>>Oldest first</option>
        </select>
      </div>
      <noscript><button type="submit" class="admin-btn">Apply</button></noscript>
    </form>
    <div class="admin-table-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contactMessages as $row): ?>
            <tr>
              <td><?= htmlspecialchars(date('M j, Y g:ia', strtotime($row['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><a href="mailto:<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></a></td>
              <td><?= htmlspecialchars($row['subject'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabels[$row['status']] ?? ucfirst($row['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><a href="message-view.php?id=<?= (int) $row['id'] ?>" class="admin-btn admin-btn-sm">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
