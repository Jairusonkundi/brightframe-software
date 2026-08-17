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
 * Scroll-reveal — fades/slides .reveal elements in as they enter the
 * viewport. Only runs when html.reveal-js is present (see header.php);
 * without it, .reveal elements have no special CSS and are just visible.
 */
document.addEventListener('DOMContentLoaded', function () {
  if (!document.documentElement.classList.contains('reveal-js')) return;
  var targets = document.querySelectorAll('.reveal');
  if (!targets.length || !('IntersectionObserver' in window)) return;

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
 * Services quick-nav — jumping to a category should reveal it even if the
 * visitor had collapsed that <details> group, and land in the right spot
 * (setting `open` after the browser has already jumped would leave the
 * page scrolled to the wrong place).
 */
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.svc-quicknav a').forEach(function (link) {
    link.addEventListener('click', function (e) {
      var target = document.querySelector(link.getAttribute('href'));
      if (!target) return;
      e.preventDefault();
      target.open = true;
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      history.pushState(null, '', link.getAttribute('href'));
    });
  });
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
