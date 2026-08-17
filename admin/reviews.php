<?php
/**
 * Admin: review moderation queue.
 *
 * Nothing submitted via reviews.php's form is public until approved
 * here. Approve/reject are simple POST actions on this same page — no
 * separate handler file, to keep the moderation flow in one place.
 *
 * NOT PROTECTED — same as admin/submissions.php. Add authentication
 * before deploying this anywhere public.
 */
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $action   = $_POST['action'] ?? '';

    if ($reviewId > 0 && in_array($action, ['approve', 'reject', 'unpublish'], true)) {
        $newStatus = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'pending');
        try {
            $stmt = $pdo->prepare('UPDATE reviews SET status = :status WHERE id = :id');
            $stmt->execute(['status' => $newStatus, 'id' => $reviewId]);
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
        "SELECT id, name, company, rating, review_text, status, submitted_at
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

$pageTitle = 'Reviews — Brightframe Software Admin';
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
  <h1>Reviews</h1>
  <p class="admin-note">
    <strong>Unprotected page.</strong> This view has no login or access control.
    Add authentication before deploying it anywhere outside local development.
  </p>

  <nav class="admin-quicknav" aria-label="Jump to section">
    <a href="submissions.php">Submissions</a>
    <a href="#admin-pending">Pending (<?= count($byStatus['pending']) ?>)</a>
    <a href="#admin-approved">Approved (<?= count($byStatus['approved']) ?>)</a>
    <a href="#admin-rejected">Rejected (<?= count($byStatus['rejected']) ?>)</a>
    <a href="stats.php">Site stats</a>
  </nav>

  <section class="admin-section" id="admin-pending">
    <h2>Pending review</h2>
    <p class="admin-section-note">Submitted via reviews.php — not visible on the site until approved.</p>

    <?php if (empty($byStatus['pending'])): ?>
      <div class="admin-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
        <p class="admin-empty-title">Nothing waiting on review</p>
        <p class="admin-empty-note">New submissions from reviews.php will show up here.</p>
      </div>
    <?php else: ?>
      <div class="admin-review-list">
        <?php foreach ($byStatus['pending'] as $review): ?>
          <div class="admin-review-card">
            <div class="admin-review-head">
              <div>
                <strong><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($review['company'])): ?>
                  <span class="admin-review-company"> · <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <span class="admin-review-rating"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
            </div>
            <p class="admin-review-text"><?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
            <div class="admin-review-meta"><?= htmlspecialchars(date('M j, Y g:ia', strtotime($review['submitted_at'])), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="admin-review-actions">
              <form method="post">
                <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit" class="admin-btn admin-btn-approve">Approve</button>
              </form>
              <form method="post">
                <input type="hidden" name="review_id" value="<?= (int) $review['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <button type="submit" class="admin-btn admin-btn-reject">Reject</button>
              </form>
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
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
        <p class="admin-empty-title">Nothing published yet</p>
        <p class="admin-empty-note">Approve a pending review above to publish it.</p>
      </div>
    <?php else: ?>
      <div class="admin-review-list">
        <?php foreach ($byStatus['approved'] as $review): ?>
          <div class="admin-review-card">
            <div class="admin-review-head">
              <div>
                <strong><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($review['company'])): ?>
                  <span class="admin-review-company"> · <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <span class="admin-review-rating"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
            </div>
            <p class="admin-review-text"><?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
            <div class="admin-review-actions">
              <form method="post">
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
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
        <p class="admin-empty-title">Nothing rejected</p>
      </div>
    <?php else: ?>
      <div class="admin-review-list">
        <?php foreach ($byStatus['rejected'] as $review): ?>
          <div class="admin-review-card admin-review-card-muted">
            <div class="admin-review-head">
              <div>
                <strong><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($review['company'])): ?>
                  <span class="admin-review-company"> · <?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
                <?php endif; ?>
              </div>
              <span class="admin-review-rating"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></span>
            </div>
            <p class="admin-review-text"><?= nl2br(htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8')) ?></p>
            <div class="admin-review-actions">
              <form method="post">
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
</div>
</body>
</html>
