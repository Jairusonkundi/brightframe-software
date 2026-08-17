<?php
/**
 * Why Choose Us — standalone page (previously index.php #why-us).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/illustrations.php';

$pageTitle       = 'Why Choose Us — Brightframe Software';
$pageDescription = 'Founder-led, full-stack under one roof, clear communication, and software built to last — here\'s why teams choose Brightframe Software.';

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="media-row reveal">
      <div class="media-row-media">
        <?= render_whyus_illustration() ?>
      </div>
      <div class="media-row-text">
        <div class="sec-eyebrow">Why work with us</div>
        <h2>Built around getting it right</h2>
        <p>No account managers, no vendor juggling, no jargon — just a direct line to the person building your software. Four things set that apart in practice:</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="whyus-grid">
      <div class="whyus-card reveal">
        <div class="whyus-ico">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h4>Founder-led</h4>
        <p>You work directly with the person building your software — not a rotating account team.</p>
      </div>
      <div class="whyus-card reveal reveal-delay-1">
        <div class="whyus-ico">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
        <h4>Full-stack, under one roof</h4>
        <p>Web, mobile, systems, ERP, branding, and marketing — no juggling five different vendors.</p>
      </div>
      <div class="whyus-card reveal reveal-delay-2">
        <div class="whyus-ico">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.55L3 20l1.05-5.4A8.5 8.5 0 1 1 21 11.5Z"/></svg>
        </div>
        <h4>Clear communication</h4>
        <p>A structured process with regular check-ins, explained in plain language — no jargon.</p>
      </div>
      <div class="whyus-card reveal reveal-delay-3">
        <div class="whyus-ico">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2 2 7v6c0 5 4 8.5 10 9 6-.5 10-4 10-9V7l-10-5Z"/><path d="m8.5 12 2.5 2.5 5-5"/></svg>
        </div>
        <h4>Built to last</h4>
        <p>Documented, maintainable systems — not just something that works on launch day.</p>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="cta-band reveal">
      <div class="cta-content">
        <div class="eyebrow"><span class="dot"></span> Ready when you are</div>
        <h2>Let's build something clear</h2>
        <p>Tell us what you're building and we'll get back to you within a day with next steps — no obligation, no sales runaround.</p>
        <div class="cta-actions">
          <a href="quote.php" class="btn btn-primary">Request a quote</a>
          <a href="contact.php" class="btn btn-outline">Contact us</a>
        </div>
        <p class="cta-note">Takes less than 5 minutes — no obligation, no commitment.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
