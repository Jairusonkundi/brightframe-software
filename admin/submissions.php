<?php
/**
 * Admin: quote requests.
 *
 * NOT PROTECTED — anyone with this URL can view lead data as-is.
 * Add authentication (e.g. a login gate, HTTP basic auth via .htaccess,
 * or an IP allowlist) before deploying this anywhere public.
 */
require_once __DIR__ . '/../config/db.php';

try {
    $submissions = $pdo->query(
        'SELECT id, name, email, phone, project_details, budget_range, timeline, submitted_at, status
         FROM quote_requests
         ORDER BY submitted_at DESC'
    )->fetchAll();

    // Selected services per request, fetched separately and grouped in PHP
    // (simpler and safer than GROUP_CONCAT for an unbounded number of services).
    $serviceRows = $pdo->query(
        'SELECT quote_request_id, service_title FROM quote_request_services ORDER BY id'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load quote requests: ' . $e->getMessage());
    $submissions = [];
    $serviceRows = [];
}

$servicesByRequest = [];
foreach ($serviceRows as $row) {
    $servicesByRequest[$row['quote_request_id']][] = $row['service_title'];
}

$pageTitle = 'Quote requests — Brightframe Software Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
<link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
<link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<div class="admin-wrap">
  <h1>Quote requests</h1>
  <p class="admin-note">
    <strong>Unprotected page.</strong> This view has no login or access control.
    Add authentication before deploying it anywhere outside local development.
  </p>

  <?php if (empty($submissions)): ?>
    <div class="admin-table-scroll">
      <div class="admin-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18v12H3z"/><path d="m3 6 9 7 9-7"/></svg>
        <p class="admin-empty-title">No quote requests yet</p>
        <p class="admin-empty-note">Requests from the "Request a quote" form will show up here as soon as someone submits one.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="admin-table-scroll">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Services</th>
            <th>Project details</th>
            <th>Budget</th>
            <th>Timeline</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($submissions as $row): ?>
            <tr>
              <td><?= htmlspecialchars(date('M j, Y g:ia', strtotime($row['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><a href="mailto:<?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></a></td>
              <td><a href="tel:<?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></a></td>
              <td>
                <?php $requestServices = $servicesByRequest[$row['id']] ?? []; ?>
                <?php if (empty($requestServices)): ?>
                  &mdash;
                <?php else: ?>
                  <div class="admin-tags">
                    <?php foreach ($requestServices as $title): ?>
                      <span class="admin-tag"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="admin-td-wrap"><?= nl2br(htmlspecialchars($row['project_details'], ENT_QUOTES, 'UTF-8')) ?></td>
              <td><?= htmlspecialchars($row['budget_range'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($row['timeline'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
              <td><span class="status-pill status-<?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(ucfirst($row['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
