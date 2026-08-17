<?php
/**
 * Reviews — public testimonials page. Only shows reviews with
 * status='approved' (see admin/reviews.php for the moderation queue).
 * No seed content on purpose: Brightframe has no completed client
 * reviews yet, so this starts genuinely empty rather than pre-filled
 * with invented testimonials — see database.sql's note on the `reviews`
 * table for the full reasoning.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/icons.php';

$pageTitle       = 'Reviews — Brightframe Software';
$pageDescription = 'What it\'s like to work with Brightframe Software, from the people who have. Worked with us? Share your own experience.';

try {
    $reviews = $pdo->query(
        "SELECT name, company, rating, review_text, submitted_at
         FROM reviews WHERE status = 'approved'
         ORDER BY display_order ASC, submitted_at DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load reviews: ' . $e->getMessage());
    $reviews = [];
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Client feedback</div>
      <h2>What it's like to work with us</h2>
      <p>Reviews from people who've worked with Brightframe Software directly — moderated before they're published, so what's here is genuine.</p>
    </div>

    <?php if (empty($reviews)): ?>
      <div class="reviews-empty reveal">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 2.5 15.1 9 22.3 10 17 15 18.3 22 12 18.6 5.7 22 7 15 1.7 10 8.9 9Z"/></svg>
        <p class="reviews-empty-title">No reviews published yet</p>
        <p class="reviews-empty-note">Brightframe Software is a new studio — we don't have published client reviews to show yet. If you've worked with us, we'd genuinely value your feedback below.</p>
      </div>
    <?php else: ?>
      <div class="review-grid reveal">
        <?php foreach ($reviews as $review): ?>
          <div class="review-card">
            <div class="review-stars"><?= render_star_rating((int) $review['rating']) ?></div>
            <p class="review-text">&ldquo;<?= htmlspecialchars($review['review_text'], ENT_QUOTES, 'UTF-8') ?>&rdquo;</p>
            <div class="review-author">
              <span class="review-name"><?= htmlspecialchars($review['name'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php if (!empty($review['company'])): ?>
                <span class="review-company"><?= htmlspecialchars($review['company'], ENT_QUOTES, 'UTF-8') ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="contact-panel reveal">
      <div class="contact-grid">
        <div class="contact-intro">
          <h2>Worked with us? Share your experience</h2>
          <p>Reviews are checked before they're published, so please leave your honest experience — good or bad. Just have a question instead? <a href="contact.php">Contact us</a>.</p>
        </div>

        <form id="review-form" novalidate>
          <div class="field">
            <label for="review-name">Your name</label>
            <input id="review-name" name="name" type="text" placeholder="Your name" required>
          </div>
          <div class="field">
            <label for="review-company">Company <span class="field-optional">optional</span></label>
            <input id="review-company" name="company" type="text" placeholder="Where you work">
          </div>
          <div class="field">
            <label>Your rating</label>
            <div class="star-input">
              <input type="radio" id="rate-5" name="rating" value="5" required><label for="rate-5" title="5 stars">★</label>
              <input type="radio" id="rate-4" name="rating" value="4"><label for="rate-4" title="4 stars">★</label>
              <input type="radio" id="rate-3" name="rating" value="3"><label for="rate-3" title="3 stars">★</label>
              <input type="radio" id="rate-2" name="rating" value="2"><label for="rate-2" title="2 stars">★</label>
              <input type="radio" id="rate-1" name="rating" value="1"><label for="rate-1" title="1 star">★</label>
            </div>
          </div>
          <div class="field">
            <label for="review-text">Your review</label>
            <textarea id="review-text" name="review_text" placeholder="What was it like working with us?" required></textarea>
          </div>
          <!-- Honeypot: hidden from real visitors, bots tend to fill every field -->
          <div class="field hp-field" aria-hidden="true">
            <label for="review-website">Leave this field blank</label>
            <input id="review-website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>
          <button type="submit" class="submit-btn">Submit review</button>
          <div id="review-form-status" class="form-status" role="status" aria-live="polite"></div>
          <p class="form-note">Reviewed before publishing — it won't appear immediately.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
