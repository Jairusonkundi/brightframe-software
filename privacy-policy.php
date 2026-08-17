<?php
/**
 * Real privacy policy content, provided directly by the site owner.
 * Not a placeholder — see terms.php for the still-pending "coming soon" page.
 */
require_once __DIR__ . '/config/db.php';

$pageTitle       = 'Privacy Policy — Brightframe Software';
$pageDescription = 'How Brightframe Software collects, uses, and protects information submitted through this website.';

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="legal-content">
      <div class="sec-eyebrow">Legal</div>
      <h1>Privacy Policy</h1>
      <p class="legal-updated">Last updated: August 14, 2026</p>

      <p>Brightframe Software Limited ("we," "us," "our") respects your privacy. This policy explains what information we collect through this website, how we use it, and how we protect it.</p>

      <h2>Information we collect</h2>
      <p>When you submit a quote request or contact form on this site, we collect the information you provide directly, which may include: your name, email address, phone number, project details, budget range, and timeline.</p>
      <p>We do not collect payment information, government ID numbers, or other sensitive personal data through this website.</p>

      <h2>How we use your information</h2>
      <p>We use the information you provide to:</p>
      <ul>
        <li>Respond to your inquiry or quote request</li>
        <li>Understand your project needs</li>
        <li>Communicate with you about our services</li>
      </ul>
      <p>We do not sell, rent, or share your information with third parties for marketing purposes.</p>

      <h2>Data storage</h2>
      <p>Information submitted through our forms is stored securely in our database and is only accessible to authorized personnel at Brightframe Software Limited.</p>

      <h2>Cookies</h2>
      <p>This site may use basic cookies to support core functionality (such as remembering form inputs during your session). We do not currently use tracking or advertising cookies.</p>

      <h2>Third-party services</h2>
      <p>We may use third-party services (such as email delivery tools) to process form submissions. These services only receive the information necessary to deliver your message to us.</p>

      <h2>Your rights</h2>
      <p>You may request that we delete any information you've submitted to us by contacting <a href="mailto:hello@brightframesoftware.com">hello@brightframesoftware.com</a>. We will respond to such requests within a reasonable timeframe.</p>

      <h2>Changes to this policy</h2>
      <p>We may update this policy from time to time. Changes will be posted on this page with an updated "Last updated" date.</p>

      <h2>Contact us</h2>
      <p>If you have questions about this policy, contact us at <a href="mailto:hello@brightframesoftware.com">hello@brightframesoftware.com</a>.</p>

      <a href="index.php" class="btn btn-primary legal-back">Back to home</a>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
