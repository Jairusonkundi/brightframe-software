<?php
/**
 * Admin: review moderation queue.
 *
 * Nothing submitted via reviews.php's form is public until approved
 * here. Approve/reject are simple POST actions on this same page — no
 * separate handler file, to keep the moderation flow in one place.
 * Rejecting can optionally record a short internal reason (reject_reason)
 * — admin-facing only, never shown anywhere on the public site.
 */
require_once __DIR__ . '/../includes/admin-auth.php';
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    admin_csrf_check();
    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    if ($reviewId > 0 && in_array($action, ['approve', 'reject', 'unpublish'], true)) {
        $newStatus = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'pending');
        try {
            if ($action === 'reject') {
                $reason = trim($_POST['reject_reason'] ?? '');
                $stmt = $pdo->prepare('UPDATE reviews SET status = :status, reject_reason = :reason WHERE id = :id');
                $stmt->execute(['status' => $newStatus, 'reason' => $reason !== '' ? $reason : null, 'id' => $reviewId]);
            } else {
                $stmt = $pdo->prepare('UPDATE reviews SET status = :status WHERE id = :id');
                $stmt->execute(['status' => $newStatus, 'id' => $reviewId]);
            }
        } catch (PDOException $e) {
            error_log('Failed to update review status: ' . $e->getMessage());
        }
    }

    // Redirect after POST so refreshing the page doesn't resubmit the action.
    header('Location: reviews.php');
    exit;
}

try {
    $allReviews = $pdo->query(
        "SELECT id, name, company, rating, review_text, status, reject_reason, submitted_at
         FROM reviews ORDER BY FIELD(status, 'pending', 'approved', 'rejected'), submitted_at DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load reviews for admin: ' . $e->getMessage());
    $allReviews = [];
}

$byStatus = ['pending' => [], 'approved' => [], 'rejected' => []];
foreach ($allReviews as $row) {
    $byStatus[$row['status']][] = $row;
}

$pageTitle   = 'Reviews';
$adminActive = 'reviews';
include __DIR__ . '/../includes/admin-layout-header.php';
?>

<div class="admin-page-header">
  <div>
    <h1>Reviews</h1>
    <p class="admin-page-sub">Moderate testimonials submitted via <a href="../reviews.php" target="_blank" rel="noopener">reviews.php</a> — nothing is public until approved here.</p>
  </div>
</div>

<nav class="admin-quicknav" aria-label="Jump to section">
  <a href="#admin-pending">Pending (<?= count($byStatus['pending']) ?>)</a>
  <a href="#admin-approved">Approved (<?= count($byStatus['approved']) ?>)</a>
  <a href="#admin-rejected">Rejected (<?= count($byStatus['rejected']) ?>)</a>
</nav>

<section class="admin-section" id="admin-pending">
  <h2>Pending review</h2>
  <p class="admin-section-note">Newest submissions first.</p>

  <?php if (empty($byStatus['pending'])): ?>
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
      </span>
      <p class="admin-empty-title">Nothing waiting on review</p>
      <p class="admin-empty-note">New submissions from reviews.php will show up here.</p>
    </div>
  <?php else: ?>
    <div class="admin-review-list">
      <?php foreach ($byStatus['pending'] as $review): ?>
        <div class="admin-review-card">
          <div class="admin-review-head">
            <div class="admin-review-person">
              <span class="admin-review-avatar"><?= htmlspecialchars(strtoupper(substr($review['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
              <div>
                <strong><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($review['company'])): ?>
                  <span class="admin-review-company"> · <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
            </div>
            <span class="admin-review-rating"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
          </div>
          <p class="admin-review-text"><?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
          <div class="admin-review-meta"><?= htmlspecialchars(date('M j, Y g:ia', strtotime($review['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="admin-review-actions">
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="admin-btn admin-btn-approve">Approve</button>
            </form>
            <details class="admin-reject-details">
              <summary class="admin-btn admin-btn-reject">Reject</summary>
              <form method="post" class="admin-reject-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <div class="admin-field">
                  <label for="reason-<?= (int) $review['id'] ?>">Reason <span class="admin-field-hint">optional, internal only</span></label>
                  <input id="reason-<?= (int) $review['id'] ?>" name="reject_reason" type="text" placeholder="e.g. couldn't verify this was a real client">
                </div>
                <button type="submit" class="admin-btn admin-btn-reject">Confirm reject</button>
              </form>
            </details>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="admin-section" id="admin-approved">
  <h2>Approved &mdash; live on reviews.php</h2>

  <?php if (empty($byStatus['approved'])): ?>
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
      </span>
      <p class="admin-empty-title">Nothing published yet</p>
      <p class="admin-empty-note">Approve a pending review above to publish it.</p>
    </div>
  <?php else: ?>
    <div class="admin-review-list">
      <?php foreach ($byStatus['approved'] as $review): ?>
        <div class="admin-review-card">
          <div class="admin-review-head">
            <div class="admin-review-person">
              <span class="admin-review-avatar"><?= htmlspecialchars(strtoupper(substr($review['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
              <div>
                <strong><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($review['company'])): ?>
                  <span class="admin-review-company"> · <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
            </div>
            <span class="admin-review-rating"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
          </div>
          <p class="admin-review-text"><?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
          <div class="admin-review-meta"><?= htmlspecialchars(date('M j, Y g:ia', strtotime($review['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></div>
          <div class="admin-review-actions">
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
              <input type="hidden" name="action" value="unpublish">
              <button type="submit" class="admin-btn">Unpublish</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="admin-section" id="admin-rejected">
  <h2>Rejected</h2>

  <?php if (empty($byStatus['rejected'])): ?>
    <div class="admin-empty">
      <span class="admin-empty-ico">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
      </span>
      <p class="admin-empty-title">Nothing rejected</p>
    </div>
  <?php else: ?>
    <div class="admin-review-list">
      <?php foreach ($byStatus['rejected'] as $review): ?>
        <div class="admin-review-card admin-review-card-muted">
          <div class="admin-review-head">
            <div class="admin-review-person">
              <span class="admin-review-avatar"><?= htmlspecialchars(strtoupper(substr($review['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
              <div>
                <strong><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($review['company'])): ?>
                  <span class="admin-review-company"> · <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
            </div>
            <span class="admin-review-rating"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
          </div>
          <p class="admin-review-text"><?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
          <?php if (!empty($review['reject_reason'])): ?>
            <div class="admin-reject-reason">Reason: <?= htmlspecialchars($review['reject_reason'], ENT_QUOTES, 'UTF-8') ?></div>
          <?php endif; ?>
          <div class="admin-review-actions">
            <form method="post">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['admin_csrf'], ENT_QUOTES, 'UTF-8') ?>">
              <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
              <input type="hidden" name="action" value="approve">
              <button type="submit" class="admin-btn admin-btn-approve">Approve instead</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<?php include __DIR__ . '/../includes/admin-layout-footer.php'; ?>
