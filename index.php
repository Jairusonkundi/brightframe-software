<?php
/**
 * Homepage.
 * Services are loaded from the `services` table instead of being hardcoded.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/icons.php';

$pageTitle       = 'Brightframe Software — Software, web, and product development';
$pageDescription = "Brightframe Software builds full-stack web applications, custom software, and the integrations that connect them — designed to be clear, reliable, and easy for your team to actually use.";

try {
    $categories = $pdo->query(
        'SELECT id, name, slug FROM service_categories ORDER BY display_order ASC'
    )->fetchAll();

    $allServices = $pdo->query(
        'SELECT id, category_id, title, description, icon_name
         FROM services ORDER BY display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load services: ' . $e->getMessage());
    $categories   = [];
    $allServices  = [];
}

// Group the flat services list under each category's id for easy looping.
$servicesByCategory = [];
foreach ($allServices as $svc) {
    $servicesByCategory[$svc['category_id']][] = $svc;
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
          <a href="#contact" class="btn btn-primary">Start a project</a>
          <a href="#services" class="btn btn-outline">See our services</a>
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
    <div class="logostrip">
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
      <div class="logostrip-item">Client logo</div>
    </div>
  </div>
</section>

<section class="section" id="about">
  <div class="wrap">
    <div class="mission-grid">
      <div class="mission-text reveal">
        <div class="sec-eyebrow">Our approach</div>
        <h2>A clear frame for every build</h2>
        <p>We're a software development company focused on building web applications and custom software that hold up under real, everyday use — not just demo day.</p>
        <p>Every project starts with a clear structure: what the system needs to do, how it connects to the tools you already use, and how your team will actually use it once it's live.</p>
      </div>
      <div class="mission-card reveal reveal-delay-1">
        <div class="vtag">Our thesis</div>
        <p>&ldquo;Good software isn't complicated. It's clear — built on a frame strong enough that everything else just fits.&rdquo;</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt" id="services">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">What we do</div>
      <h2>Services</h2>
      <p>From a first web app to full-scale systems, branding, and ongoing marketing — organized by category so you can jump straight to what you need.</p>
    </div>

    <?php if (empty($categories)): ?>
      <p style="color:var(--ink-soft);">Services are temporarily unavailable. Please check back shortly.</p>
    <?php else: ?>
      <nav class="svc-quicknav reveal" aria-label="Jump to service category">
        <?php foreach ($categories as $cat): ?>
          <a href="#cat-<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
      </nav>

      <?php $catRevealDelays = ['', 'reveal-delay-1', 'reveal-delay-2', 'reveal-delay-3']; ?>
      <?php foreach ($categories as $catIndex => $cat): ?>
        <?php $catServices = $servicesByCategory[$cat['id']] ?? []; ?>
        <?php if (empty($catServices)) continue; ?>
        <?php $catDelayClass = $catRevealDelays[$catIndex % 4]; ?>
        <details class="svc-cat reveal <?= $catDelayClass ?>" id="cat-<?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?>" open>
          <summary class="svc-cat-summary">
            <span class="svc-cat-name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="svc-cat-count"><?= count($catServices) ?> service<?= count($catServices) === 1 ? '' : 's' ?></span>
            <svg class="chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
          </summary>
          <div class="svc-cat-grid">
            <?php foreach ($catServices as $service): ?>
              <div class="svc-mini-card">
                <div class="svc-mini-ico"><?= render_service_icon($service['icon_name']) ?></div>
                <div>
                  <h3><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                  <p><?= htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </details>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</section>

<section class="section" id="process">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">How it works</div>
      <h2>A straightforward process</h2>
      <p>No jargon, no surprises — you'll know what's happening at every stage.</p>
    </div>
    <div class="process">
      <div class="proc-step reveal">
        <div class="proc-badge">1</div>
        <div class="proc-card">
          <div class="pnum">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.35-4.35"/></svg>
            Scope
          </div>
          <h4>Understand the problem</h4>
          <p>We talk through what you need, who it's for, and what "done" looks like — before any code gets written.</p>
        </div>
      </div>
      <div class="proc-step reveal reveal-delay-1">
        <div class="proc-badge">2</div>
        <div class="proc-card">
          <div class="pnum">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="m18 16 4-4-4-4M6 8l-4 4 4 4M14.5 4l-5 16"/></svg>
            Build
          </div>
          <h4>Design and develop</h4>
          <p>We build in stages, with regular check-ins, so you're never waiting weeks to see progress.</p>
        </div>
      </div>
      <div class="proc-step reveal reveal-delay-2">
        <div class="proc-badge">3</div>
        <div class="proc-card">
          <div class="pnum">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="6" cy="6" r="3"/><circle cx="18" cy="18" r="3"/><path d="M8.5 8.5l7 7"/></svg>
            Integrate
          </div>
          <h4>Connect your systems</h4>
          <p>Where needed, we wire your software up to the tools and services you already rely on.</p>
        </div>
      </div>
      <div class="proc-step reveal reveal-delay-3">
        <div class="proc-badge">4</div>
        <div class="proc-card">
          <div class="pnum">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            Support
          </div>
          <h4>Launch and maintain</h4>
          <p>Deployment, documentation, and ongoing support — so your software keeps working long after launch.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section-alt" id="team">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Who's behind it</div>
      <h2>Founder-led, hands-on</h2>
    </div>
    <div class="founder-card reveal">
      <img
        class="founder-avatar"
        src="assets/founder-placeholder.svg"
        alt="Placeholder headshot — swap in a real photo of Jairus Onkundi Morwabe at assets/founder-placeholder.svg or update the src here"
        width="104" height="104" loading="lazy">
      <div>
        <h4>Jairus Onkundi Morwabe</h4>
        <div class="role">Founder, Brightframe Software</div>
        <p>Software developer with a background spanning full-stack development and enterprise systems implementation. Every project at Brightframe Software is built with that same clarity-first thinking — from the interface down to the database underneath.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" id="why-us">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Why work with us</div>
      <h2>Built around getting it right</h2>
      <p>No account managers, no vendor juggling, no jargon — just a direct line to the person building your software.</p>
    </div>
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

<section class="section" id="contact">
  <div class="wrap">
    <div class="contact-panel reveal">
      <div class="contact-grid">
        <div class="contact-intro">
          <h2>Request a quote</h2>
          <p>Tell us what you're trying to build. We'll get back to you within a day with next steps.</p>
          <div class="contact-links">
            <a class="clink wa-btn" href="https://wa.me/254743192585" target="_blank" rel="noopener">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.9 4.44-9.9 9.9 0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.87 9.87 0 0 0 4.79 1.22h.01c5.46 0 9.9-4.44 9.9-9.9 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2Z"/></svg>
              WhatsApp: +254 743 192 585
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

        <form id="quote-form" novalidate>
          <div class="field">
            <label for="quote-category">Service category</label>
            <select id="quote-category">
              <option value="">Show all categories</option>
              <?php foreach ($categories as $cat): ?>
                <?php if (empty($servicesByCategory[$cat['id']] ?? [])) continue; ?>
                <option value="cat-<?= (int) $cat['id'] ?>"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">Narrows the checklist below — or leave it to browse everything.</p>
          </div>

          <div class="field">
            <label>Specific service(s) needed</label>
            <div class="quote-services" id="quote-services">
              <?php foreach ($categories as $cat): ?>
                <?php $catServices = $servicesByCategory[$cat['id']] ?? []; ?>
                <?php if (empty($catServices)) continue; ?>
                <fieldset class="quote-service-group" data-category="cat-<?= (int) $cat['id'] ?>">
                  <legend><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></legend>
                  <div class="quote-checkbox-grid">
                    <?php foreach ($catServices as $service): ?>
                      <label class="quote-checkbox">
                        <input type="checkbox" name="service_ids[]" value="<?= (int) $service['id'] ?>">
                        <span><?= htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8') ?></span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                </fieldset>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="field">
            <label for="project_details">Tell us about your project</label>
            <textarea id="project_details" name="project_details" placeholder="What are you building, and what does success look like?" required></textarea>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="budget_range">Budget range <span class="field-optional">optional</span></label>
              <select id="budget_range" name="budget_range">
                <option value="">Select a range</option>
                <option>Under KSh 50,000</option>
                <option>KSh 50,000&ndash;150,000</option>
                <option>KSh 150,000&ndash;500,000</option>
                <option>Above KSh 500,000</option>
                <option>Not sure yet</option>
              </select>
            </div>
            <div class="field">
              <label for="timeline">Timeline <span class="field-optional">optional</span></label>
              <select id="timeline" name="timeline">
                <option value="">Select a timeline</option>
                <option>ASAP</option>
                <option>Within 1 month</option>
                <option>1&ndash;3 months</option>
                <option>Flexible</option>
              </select>
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="name">Name</label>
              <input id="name" name="name" type="text" placeholder="Your name" required>
            </div>
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="you@company.com" required>
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" placeholder="+254 7xx xxx xxx" required>
            </div>
          </div>

          <!-- Honeypot: hidden from real visitors, bots tend to fill every field -->
          <div class="field hp-field" aria-hidden="true">
            <label for="website">Leave this field blank</label>
            <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
          </div>
          <button type="submit" class="submit-btn">Send request</button>
          <div id="form-status" class="form-status" role="status" aria-live="polite"></div>
          <p class="form-note">Sent directly to our inbox — we reply within a day.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
