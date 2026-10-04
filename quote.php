<?php
/**
 * Request a Quote — the structured project-quote form, moved off the
 * homepage. Deliberately separate from the simpler general-inquiry form
 * on contact.php. Field names/ids unchanged so handlers/quote_handler.php
 * and js/main.js keep working without modification.
 */
require_once __DIR__ . '/config/db.php';

$pageTitle       = 'Request a Quote — Brightframe Software';
$pageDescription = 'Tell us about your project and get a quote — we\'ll get back to you within a day with next steps.';

try {
    $categories = $pdo->query(
        'SELECT id, name, slug FROM service_categories ORDER BY display_order ASC'
    )->fetchAll();

    $allServices = $pdo->query(
        'SELECT id, category_id, title FROM services ORDER BY display_order ASC'
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Failed to load services: ' . $e->getMessage());
    $categories   = [];
    $allServices  = [];
}

$servicesByCategory = [];
foreach ($allServices as $svc) {
    $servicesByCategory[$svc['category_id']][] = $svc;
}

// A category page's "Request a quote for this service" button links here
// with ?category=<slug> — resolve it to the matching category id so the
// dropdown and checklist can arrive pre-narrowed instead of showing
// everything. Falls back to "show all" if the slug doesn't match anything.
$preselectedCategoryId   = null;
$preselectedCategoryName = null;
if (!empty($_GET['category'])) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $_GET['category']) {
            $preselectedCategoryId   = (int) $cat['id'];
            $preselectedCategoryName = $cat['name'];
            break;
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap">
    <div class="contact-panel reveal">
      <div class="contact-grid">
        <div class="contact-intro">
          <h2>Request a quote</h2>
          <p>Tell us about your project and get a quote — we'll get back to you within a day with next steps. Just have a question instead? <a href="contact.php">Contact us</a>.</p>
          <?php if ($preselectedCategoryName): ?>
            <p class="quote-preselect-note">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
              Pre-selected: <strong><?= htmlspecialchars($preselectedCategoryName, ENT_QUOTES, 'UTF-8') ?></strong> — change the category below to browse everything.
            </p>
          <?php endif; ?>
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

        <form id="quote-form" novalidate>
          <div class="field">
            <label for="quote-category">Service category</label>
            <select id="quote-category">
              <option value="">Show all categories</option>
              <?php foreach ($categories as $cat): ?>
                <?php if (empty($servicesByCategory[$cat['id']] ?? [])) continue; ?>
                <option value="cat-<?= (int) $cat['id'] ?>"<?= $preselectedCategoryId === (int) $cat['id'] ? ' selected' : '' ?>><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
            <p class="form-hint">Narrows the checklist below — or leave it to browse everything.</p>
          </div>

          <div class="field">
            <span class="field-title">Specific service(s) needed</span>
            <div class="quote-services" id="quote-services">
              <?php foreach ($categories as $cat): ?>
                <?php $catServices = $servicesByCategory[$cat['id']] ?? []; ?>
                <?php if (empty($catServices)) continue; ?>
                <?php $catHidden = $preselectedCategoryId !== null && $preselectedCategoryId !== (int) $cat['id']; ?>
                <fieldset class="quote-service-group" data-category="cat-<?= (int) $cat['id'] ?>"<?= $catHidden ? ' hidden' : '' ?>>
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
                <option value="Under KSh 50,000">Under KSh 50,000</option>
                <option value="KSh 50,000&ndash;150,000">KSh 50,000&ndash;150,000</option>
                <option value="KSh 150,000&ndash;500,000">KSh 150,000&ndash;500,000</option>
                <option value="Above KSh 500,000">Above KSh 500,000</option>
                <option value="Not sure yet">Not sure yet</option>
              </select>
            </div>
            <div class="field">
              <label for="timeline">Timeline <span class="field-optional">optional</span></label>
              <select id="timeline" name="timeline">
                <option value="">Select a timeline</option>
                <option value="ASAP">ASAP</option>
                <option value="Within 1 month">Within 1 month</option>
                <option value="1&ndash;3 months">1&ndash;3 months</option>
                <option value="Flexible">Flexible</option>
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
