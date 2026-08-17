<?php
/**
 * Contact Us — a simple general-inquiry form, deliberately separate from
 * the "Request a quote" form on the homepage (index.php#quote). This is
 * for someone with a question, not yet ready to describe a project
 * (that form lives at quote.php).
 */
require_once __DIR__ . '/config/db.php';

$pageTitle       = 'Contact Us — Brightframe Software';
$pageDescription = 'Have a question for Brightframe Software? Send a message and we\'ll get back to you within a day.';

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="contact-panel reveal">
      <div class="contact-grid">
        <div class="contact-intro">
          <h2>Have a question? Get in touch</h2>
          <p>Not ready to describe a full project yet? Send a quick message and we'll get back to you within a day. Ready to talk pricing instead? <a href="quote.php">Request a quote</a>.</p>
          <div class="contact-links">
            <a class="clink clink-primary" href="tel:+254743192585">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92Z"/></svg>
              Call: +254 743 192 585
            </a>
            <a class="clink" href="mailto:hello@brightframesoftware.com">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18v12H3z"/><path d="m3 6 9 7 9-7"/></svg>
              hello@brightframesoftware.com
            </a>
            <a class="clink" href="https://linkedin.com/in/jairus-onkundi" target="_blank" rel="noopener">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.14 1.45-2.14 2.94v5.66H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.56V9h3.56v11.45Z"/></svg>
              LinkedIn
            </a>
          </div>
        </div>

        <form id="contact-form" novalidate>
          <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" placeholder="Your name" required>
          </div>
          <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" placeholder="you@company.com" required>
          </div>
          <div class="field">
            <label for="subject">Subject</label>
            <input id="subject" name="subject" type="text" placeholder="What's this about?" required>
          </div>
          <div class="field">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="How can we help?" required></textarea>
          </div>
          <!-- Honeypot: hidden from real visitors, bots tend to fill every field -->
          <div class="field hp-field" aria-hidden="true">
            <label for="website">Leave this field blank</label>
            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>
          <button type="submit" class="submit-btn">Send message</button>
          <div id="form-status" class="form-status" role="status" aria-live="polite"></div>
          <p class="form-note">Sent directly to our inbox — we reply within a day.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
