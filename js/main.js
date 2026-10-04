/**
 * Theme toggle — dark/light mode switch. Persists preference in
 * localStorage under "bf-theme". The toggle button (class .theme-toggle)
 * is rendered in includes/nav.php (public site), includes/admin-layout-
 * header.php (admin panel), and admin/login.php (login page).
 *
 * An inline anti-flash script in header.php / admin-layout-header.php /
 * login.php reads localStorage before first paint to apply the saved
 * theme immediately — this handler just wires up the click behaviour
 * and syncs the button state.
 */
document.addEventListener('DOMContentLoaded', function () {
  var toggles = document.querySelectorAll('.theme-toggle');
  if (!toggles.length) return;

  function syncLabels() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    toggles.forEach(function (t) {
      t.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
      t.setAttribute('title', isDark ? 'Switch to light mode' : 'Switch to dark mode');
    });
  }

  syncLabels();

  toggles.forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      var next = isDark ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      try { localStorage.setItem('bf-theme', next); } catch (e) { /* quota or private */ }
      syncLabels();
    });
  });
});

/**
 * Admin sidebar toggle (admin/*.php, via includes/admin-layout-header.php)
 * — collapses to a hamburger-triggered drawer on narrow screens, mirroring
 * the public nav's mobile pattern.
 */
document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.getElementById('admin-sidebar-toggle');
  var sidebar = document.getElementById('admin-sidebar');
  if (!toggle || !sidebar) return;

  toggle.addEventListener('click', function () {
    var isOpen = sidebar.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });

  sidebar.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      sidebar.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
});

/**
 * Homepage device-mockup switcher — cross-fades between a few example
 * illustrations automatically. Stays on the first slide (no rotation) if
 * the visitor has requested reduced motion.
 */
document.addEventListener('DOMContentLoaded', function () {
  var switcher = document.querySelector('.hero-mockup-switch');
  if (!switcher) return;
  var slides = switcher.querySelectorAll('.hero-mockup-slide');
  if (slides.length < 2) return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  var index = 0;
  setInterval(function () {
    slides[index].classList.remove('is-active');
    index = (index + 1) % slides.length;
    slides[index].classList.add('is-active');
  }, 4000);
});

/**
 * Sticky nav — gains a shadow once the page has scrolled past the topbar,
 * giving it a subtle "lifted" feel instead of an abrupt flat-to-shadow jump.
 */
document.addEventListener('DOMContentLoaded', function () {
  var nav = document.querySelector('nav');
  if (!nav) return;

  function updateScrolledState() {
    nav.classList.toggle('is-scrolled', window.scrollY > 8);
  }
  updateScrolledState();
  window.addEventListener('scroll', updateScrolledState, { passive: true });
});

/**
 * Back-to-top button — appears once you've scrolled past roughly one
 * viewport, scrolls smoothly to the top on click.
 */
document.addEventListener('DOMContentLoaded', function () {
  var backToTop = document.getElementById('back-to-top');
  if (!backToTop) return;

  function updateVisibility() {
    backToTop.classList.toggle('is-visible', window.scrollY > 500);
  }
  updateVisibility();
  window.addEventListener('scroll', updateVisibility, { passive: true });

  backToTop.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
});

/**
 * Scroll-reveal — fades/slides .reveal elements in as they enter the
 * viewport. Only runs when html.reveal-js is present (see header.php);
 * without it, .reveal elements have no special CSS and are just visible.
 */
document.addEventListener('DOMContentLoaded', function () {
  if (!document.documentElement.classList.contains('reveal-js')) return;
  var targets = document.querySelectorAll('.reveal');
  if (!targets.length) return;
  // Inline gate in header.php already checks for IntersectionObserver, but
  // if we somehow get here without it, drop the class so the CSS fallback
  // ("just show it") takes over instead of leaving content invisible.
  if (!('IntersectionObserver' in window)) {
    document.documentElement.classList.remove('reveal-js');
    return;
  }

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

  targets.forEach(function (el) { observer.observe(el); });
});

/**
 * Main navigation — hamburger toggle on mobile, and click/keyboard support
 * for the Services/Products/Company dropdowns (hover already works via CSS
 * on desktop; this adds touch/keyboard access and the mobile accordion).
 */
document.addEventListener('DOMContentLoaded', function () {
  var navToggle = document.querySelector('.nav-toggle');
  var navLinks = document.getElementById('primary-nav');
  var dropdowns = Array.prototype.slice.call(document.querySelectorAll('.nav-dropdown'));

  if (navToggle && navLinks) {
    navToggle.addEventListener('click', function () {
      var isOpen = navToggle.getAttribute('aria-expanded') === 'true';
      navToggle.setAttribute('aria-expanded', String(!isOpen));
      navLinks.classList.toggle('is-open', !isOpen);
      if (isOpen) {
        closeAllDropdowns();
      }
    });
  }

  function closeAllDropdowns(except) {
    dropdowns.forEach(function (dd) {
      if (dd === except) return;
      dd.classList.remove('is-open');
      var toggle = dd.querySelector('.dropdown-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', 'false');
    });
  }

  dropdowns.forEach(function (dd) {
    var toggle = dd.querySelector('.dropdown-toggle');
    if (!toggle) return;
    toggle.addEventListener('click', function (e) {
      e.preventDefault();
      var isOpen = dd.classList.contains('is-open');
      closeAllDropdowns(dd);
      dd.classList.toggle('is-open', !isOpen);
      toggle.setAttribute('aria-expanded', String(!isOpen));
    });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.nav-dropdown')) {
      closeAllDropdowns();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeAllDropdowns();
      if (navToggle && navToggle.getAttribute('aria-expanded') === 'true') {
        navToggle.setAttribute('aria-expanded', 'false');
        navLinks.classList.remove('is-open');
      }
    }
  });

  // Close the mobile menu after tapping any plain nav link (not a dropdown toggle).
  if (navLinks) {
    navLinks.querySelectorAll('a.nav-link, .nav-actions-mobile a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (navToggle) navToggle.setAttribute('aria-expanded', 'false');
        navLinks.classList.remove('is-open');
      });
    });
  }
});

/**
 * Services page sidebar — highlights the category currently in view as
 * you scroll, so the sidebar always shows where you are in the catalog.
 * rootMargin shrinks the "counts as visible" band to a strip near the
 * top of the viewport, so the active link changes right as a section's
 * heading reaches it rather than whenever any part of the section shows.
 */
document.addEventListener('DOMContentLoaded', function () {
  var sidebarLinks = document.querySelectorAll('.svc-sidebar-link');
  var blocks = document.querySelectorAll('.svc-cat-block');
  if (!sidebarLinks.length || !blocks.length || !('IntersectionObserver' in window)) return;

  var linkByHash = {};
  sidebarLinks.forEach(function (link) { linkByHash[link.getAttribute('href')] = link; });

  var observer = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      var link = linkByHash['#' + entry.target.id];
      if (!link) return;
      sidebarLinks.forEach(function (l) { l.classList.remove('is-active'); });
      link.classList.add('is-active');
    });
  }, { rootMargin: '-20% 0px -70% 0px' });

  blocks.forEach(function (block) { observer.observe(block); });
});

/**
 * Quote request category filter — narrows the service checkbox groups to
 * the selected category. Progressive enhancement: without JS, every
 * group stays visible (the select's default option), so the form is
 * still fully usable.
 */
document.addEventListener('DOMContentLoaded', function () {
  var categorySelect = document.getElementById('quote-category');
  var groups = document.querySelectorAll('.quote-service-group');
  if (!categorySelect || !groups.length) return;

  categorySelect.addEventListener('change', function () {
    var selected = categorySelect.value;
    groups.forEach(function (group) {
      group.hidden = selected !== '' && group.dataset.category !== selected;
    });
  });
});

/**
 * Quote request form — submits via fetch() to handlers/quote_handler.php
 * and shows an inline success/error message without a page reload.
 */
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('quote-form');
  if (!form) return;

  var statusEl = document.getElementById('form-status');
  var submitBtn = form.querySelector('.submit-btn');

  function showStatus(message, isSuccess) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-success', 'is-error');
    statusEl.classList.add('is-visible', isSuccess ? 'is-success' : 'is-error');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending…';

    fetch('handlers/quote_handler.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.success) {
          showStatus(data.message || 'Thanks — we\'ll be in touch soon.', true);
          form.reset();
        } else {
          showStatus(data.message || 'Something went wrong. Please try again.', false);
        }
      })
      .catch(function () {
        showStatus('Could not reach the server. Please try again in a moment.', false);
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send request';
      });
  });
});

/**
 * Contact form (contact.php) — the simple general-inquiry form, submits
 * via fetch() to handlers/contact_handler.php. Separate from the quote
 * form above (different form id, different endpoint, different page).
 */
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('contact-form');
  if (!form) return;

  var statusEl = document.getElementById('form-status');
  var submitBtn = form.querySelector('.submit-btn');

  function showStatus(message, isSuccess) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-success', 'is-error');
    statusEl.classList.add('is-visible', isSuccess ? 'is-success' : 'is-error');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending…';

    fetch('handlers/contact_handler.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.success) {
          showStatus(data.message || 'Thanks — we\'ll be in touch soon.', true);
          form.reset();
        } else {
          showStatus(data.message || 'Something went wrong. Please try again.', false);
        }
      })
      .catch(function () {
        showStatus('Could not reach the server. Please try again in a moment.', false);
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send message';
      });
  });
});

/**
 * Review form (reviews.php) — submits via fetch() to
 * handlers/review_handler.php. A submitted review is never shown back to
 * its own submitter or anyone else immediately — it lands as 'pending'
 * and only appears once an admin approves it in admin/reviews.php.
 */
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('review-form');
  if (!form) return;

  var statusEl = document.getElementById('review-form-status');
  var submitBtn = form.querySelector('.submit-btn');

  function showStatus(message, isSuccess) {
    statusEl.textContent = message;
    statusEl.classList.remove('is-success', 'is-error');
    statusEl.classList.add('is-visible', isSuccess ? 'is-success' : 'is-error');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting…';

    fetch('handlers/review_handler.php', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(form)
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.success) {
          showStatus(data.message || 'Thanks for sharing your experience.', true);
          form.reset();
        } else {
          showStatus(data.message || 'Something went wrong. Please try again.', false);
        }
      })
      .catch(function () {
        showStatus('Could not reach the server. Please try again in a moment.', false);
      })
      .finally(function () {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit review';
      });
  });
});
