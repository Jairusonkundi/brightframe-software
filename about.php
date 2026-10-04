<?php
/**
 * About Us — mission/approach, process, and founder/team, combined into
 * one standalone page (previously the index.php #about/#process/#team
 * sections).
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/illustrations.php';

$pageTitle       = 'About Us — Brightframe Software';
$pageDescription = 'Founder-led software development company building full-stack web applications and custom software with a clear, structured process — from scope to launch.';

include __DIR__ . '/includes/header.php';
?>

<section class="section" id="about">
  <div class="wrap">
    <div class="media-row reveal">
      <div class="media-row-media">
        <?= render_mission_illustration() ?>
      </div>
      <div class="media-row-text">
        <div class="sec-eyebrow">Our approach</div>
        <h2>A clear frame for every build</h2>
        <p>We're a software development company focused on building web applications and custom software that hold up under real, everyday use — not just demo day.</p>
        <p>Every project starts with a clear structure: what the system needs to do, how it connects to the tools you already use, and how your team will actually use it once it's live.</p>
      </div>
    </div>
    <div class="mission-card mission-card-standalone reveal reveal-delay-1">
      <div class="vtag">Our thesis</div>
      <p>&ldquo;Good software isn't complicated. It's clear — built on a frame strong enough that everything else just fits.&rdquo;</p>
    </div>
  </div>
</section>

<section class="section section-alt" id="process">
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

<section class="section" id="team">
  <div class="wrap">
    <div class="sec-head reveal">
      <div class="sec-eyebrow">Who's behind it</div>
      <h2>Founder-led, hands-on</h2>
    </div>
    <div class="founder-card reveal">
      <img
        class="founder-avatar"
        src="assets/founder-placeholder.svg"
        alt="Jairus Onkundi Morwabe, founder of Brightframe Software (photo coming soon)"
        width="104" height="104" loading="lazy">
      <div>
        <h4>Jairus Onkundi Morwabe</h4>
        <div class="role">Founder, Brightframe Software</div>
        <p>Software developer with a background spanning full-stack development and enterprise systems implementation. Every project at Brightframe Software is built with that same clarity-first thinking — from the interface down to the database underneath.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
