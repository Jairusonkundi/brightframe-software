<?php
/**
 * Homepage — kept minimal by design: hero, trust strip, a condensed
 * services overview, a short why-us teaser, and a CTA band pointing to
 * the dedicated services.php / why-us.php / quote.php pages, which carry
 * the full detail.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/icons.php';
require_once __DIR__ . '/includes/illustrations.php';

$pageTitle       = 'Brightframe Software — Software, web, and product development';
$pageDescription = "Brightframe Software builds full-stack web applications, custom software, and the integrations that connect them — designed to be clear, reliable, and easy for your team to actually use.";

try {
    $categories = $pdo->query(
        'SELECT id, name, slug FROM service_categories ORDER BY display_order ASC'
    )->fetchAll();

    // Example service titles for the homepage cards' tags — 3 per
    // category, in display order, via a windowed query rather than
    // fetching every service and trimming in PHP.
    $exampleServiceRows = $pdo->query(
        'SELECT category_id, title FROM (
           SELECT category_id, title, display_order,
                  ROW_NUMBER() OVER (PARTITION BY category_id ORDER BY display_order ASC) AS rn
           FROM services
         ) ranked
         WHERE rn <= 3'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load service categories: ' . $e->getMessage());
    $categories         = [];
    $exampleServiceRows = [];
}

$exampleServicesByCategory = [];
foreach ($exampleServiceRows as $row) {
    $exampleServicesByCategory[$row['category_id']][] = $row['title'];
}

include __DIR__ . '/includes/header.php';
?>

<header class="hero">
  <div class="hero-grid"></div>
  <div class="wrap hero-inner">
    <div class="hero-layout">
      <div class="hero-copy">
        <div class="eyebrow"><span class="dot"></span> Now taking on new projects</div>
        <h1>Software that gives your business a <span>clear frame to work from</span>.</h1>
        <p class="lede">Brightframe Software builds full-stack web applications, custom software, and the integrations that connect them — designed to be clear, reliable, and easy for your team to actually use.</p>
        <div class="hero-actions">
          <a href="quote.php" class="btn btn-primary">Start a project</a>
          <a href="services.php" class="btn btn-outline">See our services</a>
        </div>
        <div class="hero-stats">
          <div class="item"><div class="n">Full-stack</div><div class="l">Web and software development</div></div>
          <div class="item"><div class="n">Integration-ready</div><div class="l">Built to connect with your systems</div></div>
          <div class="item"><div class="n">End-to-end</div><div class="l">From build to launch support</div></div>
        </div>
      </div>

      <div class="hero-art" aria-hidden="true">
        <svg viewBox="0 0 480 440" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="heroLine" x1="0" y1="0" x2="480" y2="440" gradientUnits="userSpaceOnUse">
              <stop offset="0" stop-color="#5B5FEF"/>
              <stop offset="1" stop-color="#00D9C0"/>
            </linearGradient>
            <linearGradient id="heroBar" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0" stop-color="#00D9C0"/>
              <stop offset="1" stop-color="#5B5FEF"/>
            </linearGradient>
            <pattern id="heroDots" width="26" height="26" patternUnits="userSpaceOnUse">
              <circle cx="2" cy="2" r="1.4" fill="#ffffff" opacity="0.14"/>
            </pattern>
          </defs>

          <rect x="0" y="0" width="480" height="440" fill="url(#heroDots)"/>
          <rect x="46" y="34" width="380" height="360" rx="26" stroke="url(#heroLine)" stroke-width="1.5" stroke-dasharray="5 7" opacity="0.55"/>

          <!-- Main dashboard panel -->
          <g>
            <rect x="76" y="86" width="228" height="150" rx="16" fill="rgba(255,255,255,0.05)" stroke="rgba(255,255,255,0.16)"/>
            <circle cx="100" cy="112" r="5" fill="#00D9C0"/>
            <rect x="116" y="107" width="70" height="10" rx="5" fill="rgba(255,255,255,0.32)"/>
            <rect x="96" y="136" width="188" height="7" rx="3.5" fill="rgba(255,255,255,0.16)"/>
            <rect x="96" y="152" width="140" height="7" rx="3.5" fill="rgba(255,255,255,0.16)"/>
            <rect x="216" y="184" width="14" height="34" rx="3" fill="url(#heroBar)" opacity="0.9"/>
            <rect x="238" y="196" width="14" height="22" rx="3" fill="url(#heroBar)" opacity="0.65"/>
            <rect x="260" y="174" width="14" height="44" rx="3" fill="url(#heroBar)"/>
            <rect x="96" y="184" width="100" height="34" rx="8" fill="rgba(0,217,192,0.1)" stroke="rgba(0,217,192,0.3)"/>
            <path d="M110 201 L118 209 L134 191" stroke="#00D9C0" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
          </g>

          <!-- Status pill panel, top right -->
          <g>
            <rect x="300" y="56" width="120" height="42" rx="21" fill="rgba(0,217,192,0.12)" stroke="rgba(0,217,192,0.35)"/>
            <circle cx="322" cy="77" r="5" fill="#00D9C0"/>
            <rect x="336" y="72" width="66" height="9" rx="4.5" fill="rgba(255,255,255,0.5)"/>
          </g>

          <!-- Secondary card, lower right -->
          <g>
            <rect x="252" y="252" width="150" height="104" rx="14" fill="rgba(91,95,239,0.14)" stroke="rgba(91,95,239,0.35)"/>
            <circle cx="278" cy="280" r="14" fill="rgba(91,95,239,0.35)"/>
            <path d="M271 280 L276 285 L286 273" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="300" y="273" width="82" height="8" rx="4" fill="rgba(255,255,255,0.4)"/>
            <rect x="272" y="308" width="110" height="7" rx="3.5" fill="rgba(255,255,255,0.18)"/>
            <rect x="272" y="324" width="80" height="7" rx="3.5" fill="rgba(255,255,255,0.18)"/>
          </g>

          <!-- Connector lines linking the panels ("integration") -->
          <path d="M304 160 C 330 160, 330 200, 300 210" stroke="url(#heroLine)" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.7"/>
          <circle cx="300" cy="210" r="3.5" fill="#00D9C0"/>
          <path d="M270 236 C 270 260, 300 255, 300 252" stroke="url(#heroLine)" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.7"/>
          <circle cx="270" cy="236" r="3.5" fill="#5B5FEF"/>
          <path d="M310 98 C 330 90, 330 75, 320 66" stroke="url(#heroLine)" stroke-width="1.5" stroke-dasharray="3 6" opacity="0.7"/>
          <circle cx="310" cy="98" r="3.5" fill="#5B5FEF"/>

          <!-- Floating particles for depth -->
          <circle cx="94" cy="270" r="3" fill="#5B5FEF" opacity="0.5"/>
          <circle cx="360" cy="380" r="4" fill="#00D9C0" opacity="0.4"/>
          <circle cx="400" cy="150" r="2.5" fill="#ffffff" opacity="0.4"/>
        </svg>
      </div>
    </div>
  </div>
</header>

<section class="logostrip-section reveal" aria-label="Companies we've worked with">
  <div class="wrap">
    <div class="logostrip-head">Companies we've worked with</div>
  </div>
  <div class="logostrip-marquee">
    <div class="logostrip-track">
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <!-- Same 6 items again, back to back — creates a seamless loop when
           the track scrolls exactly -50% (see @keyframes logostrip-scroll).
           Hidden from assistive tech so it doesn't announce "Client logo" twice. -->
      <div class="logostrip-item" aria-hidden="true">Client logo</div>
      <div class="logostrip-item" aria-hidden="true">Client logo</div>
      <div class="logostrip-item" aria-hidden="true">Client logo</div>
      <div class="logostrip-item" aria-hidden="true">Client logo</div>
      <div class="logostrip-item" aria-hidden="true">Client logo</div>
      <div class="logostrip-item" aria-hidden="true">Client logo</div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/site-stats.php'; ?>

<section class="section section-alt">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">What we do</div>
      <h2>Services</h2>
      <p>Full-stack web and software development, plus the branding and marketing that gets it in front of people — organized into eight areas.</p>
    </div>

    <?php if (empty($categories)): ?>
      <p style="color:var(--ink-soft);">Services are temporarily unavailable. Please check back shortly.</p>
    <?php else: ?>
      <?php $ovRevealDelays = ['', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3']; ?>
      <div class="svc-overview-grid">
        <?php foreach ($categories as $i => $cat): ?>
          <?php $ovDelayClass = $ovRevealDelays[$i % 4]; ?>
          <a class="svc-overview-card reveal <?= $ovDelayClass ?>" href="services/<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>.php">
            <div class="svc-overview-ico"><?= render_service_icon($cat['slug']) ?></div>
            <h3><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p><?= htmlspecialchars(category_blurb($cat['slug']), ENT_QUOTES, 'UTF-8') ?></p>
            <?php if (!empty($exampleServicesByCategory[$cat['id']])): ?>
              <div class="svc-overview-tags">
                <?php foreach ($exampleServicesByCategory[$cat['id']] as $exampleTitle): ?>
                  <span class="svc-overview-tag"><?= htmlspecialchars($exampleTitle, ENT_QUOTES, 'UTF-8') ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <span class="svc-overview-link">
              Learn more
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="svc-overview-cta">
        <a href="services.php" class="btn-text-cta">
          View all services
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Built for every screen</div>
      <h2>Web and mobile, done right</h2>
      <p>A look at the kind of interfaces we build — clean, fast, and easy to actually use, whether that's a browser tab, a phone in someone's hand, or an internal dashboard.</p>
    </div>
    <div class="hero-mockup-switch reveal" role="group" aria-label="Examples of the interfaces we build, rotating automatically">
      <div class="hero-mockup-slide is-active"><?= render_category_illustration('web-development-design') ?></div>
      <div class="hero-mockup-slide"><?= render_category_illustration('mobile-development') ?></div>
      <div class="hero-mockup-slide"><?= render_category_illustration('erp-business-systems') ?></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="wrap">
    <div class="whyus-teaser-grid">
      <div class="whyus-teaser-intro reveal">
        <div class="sec-eyebrow">Why work with us</div>
        <h2>Built around getting it right</h2>
        <p>No account managers, no vendor juggling, no jargon — just a direct line to the person building your software.</p>
        <a href="why-us.php" class="btn-text-cta">
          See why teams choose us
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
      <div class="whyus-teaser-list">
        <div class="whyus-teaser-item reveal reveal-delay-1">
          <div class="whyus-teaser-ico">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
          </div>
          <div>
            <h4>Founder-led</h4>
            <p>You work directly with the person building your software — not a rotating account team.</p>
          </div>
        </div>
        <div class="whyus-teaser-item reveal reveal-delay-2">
          <div class="whyus-teaser-ico">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          </div>
          <div>
            <h4>Full-stack, under one roof</h4>
            <p>Web, mobile, systems, ERP, branding, and marketing — no juggling five different vendors.</p>
          </div>
        </div>
        <div class="whyus-teaser-item reveal reveal-delay-3">
          <div class="whyus-teaser-ico">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.5 8.5 0 0 1-12.4 7.55L3 20l1.05-5.4A8.5 8.5 0 1 1 21 11.5Z"/></svg>
          </div>
          <div>
            <h4>Clear communication</h4>
            <p>A structured process with regular check-ins, explained in plain language — no jargon.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <div class="cta-band reveal">
      <div class="cta-content">
        <div class="eyebrow"><span class="dot"></span> Free project consultation</div>
        <h2>Ready to build something clear?</h2>
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
