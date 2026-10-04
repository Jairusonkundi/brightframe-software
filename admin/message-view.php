<?php
/**
 * Admin: single contact message — full detail + status change.
 * Viewing a 'new' message auto-advances it to 'read' (standard inbox
 * behavior) — doesn't touch it if it's already 'read' or 'replied'.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: messages.php');
    exit;
}

$validStatuses = ['new', 'read', 'replied'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $validStatuses, true)) {
        try {
            $stmt = $pdo->prepare('UPDATE contact_messages SET status = :status WHERE id = :id');
            $stmt->execute(['status' => $newStatus, 'id' => $id]);
        } catch (PDOException $e) {
            error_log('Failed to update contact message status: ' . $e->getMessage());
        }
    }
    header('Location: message-view.php?id=' . $id . '&saved=1');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, subject, message, submitted_at, status
         FROM contact_messages WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $message = $stmt->fetch();

    // Auto-advance new -> read on view.
    if ($message && $message['status'] === 'new') {
        $pdo->prepare("UPDATE contact_messages SET status = 'read' WHERE id = :id")->execute(['id' => $id]);
        $message['status'] = 'read';
    }
} catch (PDOException $e) {
    error_log('Failed to load contact message: ' . $e->getMessage());
    $message = false;
}

if (!$message) {
    header('Location: messages.php');
    exit;
}

$statusLabels = ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied'];

$pageTitle   = 'Message #' . $id;
$adminActive = 'messages';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-breadcrumb"><a href="messages.php">Contact Messages</a> <span aria-hidden="true">/</span> #<?= $id ?></div>
<div class="admin-page-header">
  <div>
    <h1><?= htmlspecialchars($message['subject'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="admin-page-sub">From <?= htmlspecialchars($message['name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars(date('M j, Y \a\t g:ia', strtotime($message['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></p>
  </div>
  <span class="status-pill status-<?= htmlspecialchars($message['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabels[$message['status']] ?? ucfirst($message['status']), ENT_QUOTES, 'UTF-8') ?></span>
</div>

<?php if (isset($_GET['saved'])): ?>
  <p class="admin-saved-note">Status updated.</p>
<?php endif; ?>

<div class="admin-detail-grid">
  <div class="admin-panel">
    <h2>Message</h2>
    <div class="admin-detail-field" style="margin-top: 16px;">
      <div class="admin-detail-value"><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')) ?></div>
    </div>
  </div>

  <div>
    <div class="admin-panel" style="margin-bottom: 18px;">
      <h2>Contact</h2>
      <div class="admin-detail-field" style="margin-top: 14px;">
        <div class="admin-detail-label">Email</div>
        <div class="admin-detail-value"><a href="mailto:<?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?></a></div>
      </div>
      <a href="mailto:<?= htmlspecialchars($message['email'], ENT_QUOTES, 'UTF-8') ?>?subject=Re: <?= rawurlencode($message['subject']) ?>" class="admin-btn admin-btn-primary" style="width:100%; justify-content:center;">Reply by email</a>
    </div>

    <div class="admin-status-panel">
      <h2>Status</h2>
      <p class="admin-panel-note">Mark as replied once you've responded.</p>
      <form method="post" class="admin-status-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="admin-field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statusLabels as $value => $label): ?>
              <option value="<?= $value ?>"<?= $message['status'] === $value ? ' selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary">Save status</button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
