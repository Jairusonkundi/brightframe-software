<?php
/**
 * Admin: single quote request — full detail + status change.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: quotes.php');
    exit;
}

$validStatuses = ['new', 'contacted', 'in_discussion', 'closed'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, $validStatuses, true)) {
        try {
            $stmt = $pdo->prepare('UPDATE quote_requests SET status = :status WHERE id = :id');
            $stmt->execute(['status' => $newStatus, 'id' => $id]);
        } catch (PDOException $e) {
            error_log('Failed to update quote request status: ' . $e->getMessage());
        }
    }
    header('Location: quote-view.php?id=' . $id . '&saved=1');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, name, email, phone, project_details, budget_range, timeline, submitted_at, status
         FROM quote_requests WHERE id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $quote = $stmt->fetch();

    $servicesStmt = $pdo->prepare('SELECT service_title FROM quote_request_services WHERE quote_request_id = :id ORDER BY id');
    $servicesStmt->execute(['id' => $id]);
    $requestServices = $servicesStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log('Failed to load quote request: ' . $e->getMessage());
    $quote = false;
    $requestServices = [];
}

if (!$quote) {
    header('Location: quotes.php');
    exit;
}

$statusLabels = ['new' => 'New', 'contacted' => 'Contacted', 'in_discussion' => 'In discussion', 'closed' => 'Closed'];

$pageTitle   = 'Quote #' . $id;
$adminActive = 'quotes';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-breadcrumb"><a href="quotes.php">Quote Requests</a> <span aria-hidden="true">/</span> #<?= $id ?></div>
<div class="admin-page-header">
  <div>
    <h1><?= htmlspecialchars($quote['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="admin-page-sub">Submitted <?= htmlspecialchars(date('M j, Y \a\t g:ia', strtotime($quote['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></p>
  </div>
  <span class="status-pill status-<?= htmlspecialchars($quote['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabels[$quote['status']] ?? ucfirst($quote['status']), ENT_QUOTES, 'UTF-8') ?></span>
</div>

<?php if (isset($_GET['saved'])): ?>
  <p class="admin-saved-note">Status updated.</p>
<?php endif; ?>

<div class="admin-detail-grid">
  <div class="admin-panel">
    <h2>Project details</h2>
    <div class="admin-detail-field" style="margin-top: 16px;">
      <div class="admin-detail-value"><?= nl2br(htmlspecialchars($quote['project_details'], ENT_QUOTES, 'UTF-8')) ?></div>
    </div>

    <div class="admin-detail-field">
      <div class="admin-detail-label">Services requested</div>
      <?php if (empty($requestServices)): ?>
        <div class="admin-detail-value">&mdash;</div>
      <?php else: ?>
        <div class="admin-tags">
          <?php foreach ($requestServices as $title): ?>
            <span class="admin-tag"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="admin-form-row">
      <div class="admin-detail-field">
        <div class="admin-detail-label">Budget range</div>
        <div class="admin-detail-value"><?= htmlspecialchars($quote['budget_range'] ?: 'Not specified', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div class="admin-detail-field">
        <div class="admin-detail-label">Timeline</div>
        <div class="admin-detail-value"><?= htmlspecialchars($quote['timeline'] ?: 'Not specified', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
    </div>
  </div>

  <div>
    <div class="admin-panel" style="margin-bottom: 18px;">
      <h2>Contact</h2>
      <div class="admin-detail-field" style="margin-top: 14px;">
        <div class="admin-detail-label">Email</div>
        <div class="admin-detail-value"><a href="mailto:<?= htmlspecialchars($quote['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($quote['email'], ENT_QUOTES, 'UTF-8') ?></a></div>
      </div>
      <div class="admin-detail-field">
        <div class="admin-detail-label">Phone</div>
        <div class="admin-detail-value"><a href="tel:<?= htmlspecialchars($quote['phone'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($quote['phone'], ENT_QUOTES, 'UTF-8') ?></a></div>
      </div>
      <a href="mailto:<?= htmlspecialchars($quote['email'], ENT_QUOTES, 'UTF-8') ?>?subject=Re: your quote request" class="admin-btn admin-btn-primary" style="width:100%; justify-content:center;">Reply by email</a>
    </div>

    <div class="admin-status-panel">
      <h2>Status</h2>
      <p class="admin-panel-note">Update as you progress through the conversation.</p>
      <form method="post" class="admin-status-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="admin-field">
          <label for="status">Status</label>
          <select id="status" name="status">
            <?php foreach ($statusLabels as $value => $label): ?>
              <option value="<?= $value ?>"<?= $quote['status'] === $value ? ' selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="admin-btn admin-btn-primary">Save status</button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
