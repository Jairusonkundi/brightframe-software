<?php
/**
 * Footer is DB-driven for its Services column (same service_categories
 * table as the nav and homepage) — self-contained like nav.php, so it
 * works regardless of what the including page already loaded.
 */
require_once __DIR__ . '/../config/db.php';

try {
    $footerCategories = $pdo->query(
        'SELECT name, slug FROM service_categories ORDER BY display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load footer service categories: ' . $e->getMessage());
    $footerCategories = [];
}

// Set by pages one level deep (services/<slug>.php) before including this
// file directly — defaults to root-relative.
$basePath = $basePath ?? '';
?>
<footer>
  <div class="wrap">
    <div class="footer-grid">
      <div class="footer-col footer-col-brand">
        <div class="logo footer-logo">
          <svg width="26" height="26" viewBox="0 0 32 32" role="img" aria-label="Brightframe Software logo">
            <defs>
              <linearGradient id="logoSparkFooter" x1="10" y1="10" x2="22" y2="22" gradientUnits="userSpaceOnUse">
                <stop offset="0" stop-color="#5B5FEF"/>
                <stop offset="1" stop-color="#00D9C0"/>
              </linearGradient>
            </defs>
            <path d="M6 13 L6 7 L13 7" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M19 7 L26 7 L26 13" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6 19 L6 25 L13 25" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M26 19 L26 25 L19 25" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M16 11 L17.3 14.7 L21 16 L17.3 17.3 L16 21 L14.7 17.3 L11 16 L14.7 14.7 Z" fill="url(#logoSparkFooter)"/>
          </svg>
          Brightframe Software
        </div>
        <p class="footer-tagline">Full-stack web, mobile, and business software — built clear, built to last.</p>

        <!-- Same social icon set as includes/topbar.php (LinkedIn/GitHub/X are
             Jairus's personal links; Facebook/Instagram are non-linked
             placeholders) — see the comment there for details. -->
        <div class="footer-social">
          <a href="https://linkedin.com/in/jairus-onkundi" target="_blank" rel="noopener" aria-label="Jairus Onkundi on LinkedIn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.02-3.03-1.85-3.03-1.85 0-2.14 1.45-2.14 2.94v5.66H9.35V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.56V9h3.56v11.45Z"/></svg>
          </a>
          <a href="https://github.com/jairusonkundi" target="_blank" rel="noopener" aria-label="Jairus Onkundi on GitHub">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.221-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .268.18.58.688.482A10.019 10.019 0 0 0 22 12.017C22 6.484 17.522 2 12 2Z"/></svg>
          </a>
          <a href="https://x.com/jairus_onkundi" target="_blank" rel="noopener" aria-label="Jairus Onkundi on X">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-7.6 8.7L23.3 22h-6.9l-5.4-6.9L4.8 22H1.7l8.1-9.3L1 2h7.1l4.9 6.3L18.9 2Zm-1.2 18h1.9L7.4 4h-2l12.3 16Z"/></svg>
          </a>
          <!-- Placeholder — no Facebook account yet. Add href + swap span for a once one exists. -->
          <span class="footer-social-disabled" title="Facebook — coming soon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-7.5h2.5l.5-3h-3V8.5c0-.87.24-1.46 1.5-1.46H16.5V4.34C16.24 4.3 15.36 4.22 14.33 4.22c-2.15 0-3.62 1.31-3.62 3.72V10.5H8.2v3h2.5V21h2.8Z"/></svg>
          </span>
          <!-- Placeholder — no Instagram account yet. Add href + swap span for a once one exists. -->
          <span class="footer-social-disabled" title="Instagram — coming soon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1"/></svg>
          </span>
        </div>
      </div>

      <div class="footer-col">
        <div class="footer-col-head">Company</div>
        <a href="<?= $basePath ?>about.php">About Us</a>
        <a href="<?= $basePath ?>about.php#team">Our Team</a>
        <a href="<?= $basePath ?>why-us.php">Why Choose Us</a>
        <a href="<?= $basePath ?>reviews.php">Reviews</a>
        <a href="<?= $basePath ?>contact.php">Contact Us</a>
      </div>

      <div class="footer-col">
        <div class="footer-col-head">Services</div>
        <?php foreach ($footerCategories as $cat): ?>
          <a href="<?= $basePath ?>services/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>.php"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
      </div>

      <div class="footer-col">
        <div class="footer-col-head">Contact</div>
        <a href="mailto:hello@brightframesoftware.com">hello@brightframesoftware.com</a>
        <a href="https://wa.me/254743192585" target="_blank" rel="noopener">WhatsApp: +254 743 192 585</a>
        <span class="footer-location">Nairobi, Kenya</span>
      </div>
    </div>

    <div class="footer-bottom">
      <div>&copy; <?= date('Y') ?> Brightframe Software Limited &middot; Nairobi, Kenya</div>
      <div class="footer-legal">
        <a href="<?= $basePath ?>privacy-policy.php">Privacy Policy</a>
        <a href="<?= $basePath ?>terms.php">Terms</a>
      </div>
    </div>
  </div>
</footer>

<!-- Site-wide floating actions: back-to-top + WhatsApp widget. Fixed
     position, so DOM placement doesn't matter — this lives in the footer
     include so it renders once on every page. -->
<div class="floating-actions">
  <button type="button" id="back-to-top" aria-label="Back to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
  </button>
  <a class="whatsapp-widget" href="https://wa.me/254743192585" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <svg width="30" height="30" viewBox="0 0 24 24" aria-hidden="true">
      <path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Z" fill="#fff"/>
      <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" fill="#25D366"/>
    </svg>
  </a>
</div>

<script src="<?= $basePath ?>js/main.js?v=<?= @filemtime(__DIR__ . '/../js/main.js') ?: '1' ?>"></script>
</body>
</html>
