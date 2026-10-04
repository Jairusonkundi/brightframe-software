<?php
/**
 * Admin: quote requests list. Full detail + status changes happen on
 * quote-view.php — this page is just the filterable/sortable overview.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

$validStatuses = ['new', 'contacted', 'in_discussion', 'closed'];
$statusFilter  = $_GET['status'] ?? '';
if (!in_array($statusFilter, $validStatuses, true)) {
    $statusFilter = '';
}
$sort = ($_GET['sort'] ?? 'newest') === 'oldest' ? 'oldest' : 'newest';

try {
    $sql = 'SELECT id, name, email, phone, budget_range, timeline, submitted_at, status FROM quote_requests';
    $params = [];
    if ($statusFilter !== '') {
        $sql .= ' WHERE status = :status';
        $params['status'] = $statusFilter;
    }
    $sql .= ' ORDER BY submitted_at ' . ($sort === 'oldest' ? 'ASC' : 'DESC');
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $quoteRequests = $stmt->fetchAll();

    $serviceRows = $pdo->query(
        'SELECT quote_request_id, service_title FROM quote_request_services ORDER BY id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load quote requests: ' . $e->getMessage());
    $quoteRequests = [];
    $serviceRows   = [];
}

$servicesByRequest = [];
foreach ($serviceRows as $row) {
    $servicesByRequest[$row['quote_request_id']][] = $row['service_title'];
}

$statusLabels = ['new' => 'New', 'contacted' => 'Contacted', 'in_discussion' => 'In discussion', 'closed' => 'Closed'];

$pageTitle   = 'Quote Requests';
$adminActive = 'quotes';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Quote Requests</h1>
    <p class="admin-page-sub">Leads from the "Request a quote" form — track each one from first contact through to closure.</p>
  </div>
</div>

<?php if (empty($quoteRequests)): ?>
  <div class="admin-panel">
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h4"/></svg>
      </span>
      <p class="admin-empty-title"><?= $statusFilter !== '' ? 'No requests with this status' : 'No quote requests yet' ?></p>
      <p class="admin-empty-note">
        <?= $statusFilter !== '' ? 'Try a different status filter above.' : 'Requests from the "Request a quote" form will show up here as soon as someone submits one.' ?>
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
            <th>Services</th>
            <th>Budget</th>
            <th>Timeline</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($quoteRequests as $row): ?>
            <tr>
              <td><?= htmlspecialchars(date('M j, Y g:ia', strtotime($row['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><a href="mailto:<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></a></td>
              <td>
                <?php $requestServices = $servicesByRequest[$row['id']] ?? []; ?>
                <?php if (empty($requestServices)): ?>
                  &mdash;
                <?php else: ?>
                  <div class="admin-tags">
                    <?php foreach (array_slice($requestServices, 0, 3) as $title): ?>
                      <span class="admin-tag"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                    <?php if (count($requestServices) > 3): ?>
                      <span class="admin-tag">+<?= count($requestServices) - 3 ?> more</span>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($row['budget_range'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['timeline'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($statusLabels[$row['status']] ?? ucfirst($row['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><a href="quote-view.php?id=<?= (int) $row['id'] ?>" class="admin-btn admin-btn-sm">View</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
