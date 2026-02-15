/* ============================================
   ECORIDE - Shared JavaScript Utilities
   Pure Vanilla JS - No Frameworks
   ============================================ */

function countUp(element, target, duration, suffix) {
  if (!element) return;

  suffix = suffix || '';
  duration = duration || 2500;

  var startTime = null;

  function animate(timestamp) {
    if (!startTime) startTime = timestamp;

    var progress = Math.min((timestamp - startTime) / duration, 1);
    var eased = 1 - Math.pow(1 - progress, 3);
    var current = Math.floor(eased * target);

    element.textContent = current.toLocaleString() + suffix;

    if (progress < 1) {
      requestAnimationFrame(animate);
    }
  }

  requestAnimationFrame(animate);
}

function initCountUpElements() {
  var elements = document.querySelectorAll('[data-count]');
  if (!elements.length) return;

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !entry.target.dataset.counted) {
          entry.target.dataset.counted = 'true';

          var target = parseInt(entry.target.dataset.count, 10);
          var suffix = entry.target.dataset.suffix || '';
          var duration = parseInt(entry.target.dataset.duration, 10) || 2500;

          countUp(entry.target, target, duration, suffix);
        }
      });
    },
    { threshold: 0.3 }
  );

  elements.forEach(function (el) {
    observer.observe(el);
  });
}

function initScrollAnimations() {
  var elements = document.querySelectorAll('[data-animate]');
  if (!elements.length) return;

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          var delay = entry.target.dataset.delay || 0;
          setTimeout(function () {
            entry.target.classList.add('animate-fadeIn');
            entry.target.style.opacity = '1';
          }, delay * 1000);

          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.1 }
  );

  elements.forEach(function (el) {
    el.style.opacity = '0';
    observer.observe(el);
  });
}

function closeModal(modalId) {
  var modal = document.getElementById(modalId);
  if (!modal) return;

  modal.classList.remove('active');
  document.body.style.overflow = '';
}

function initModals() {
  document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === this) {
        this.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  });

  document.querySelectorAll('[data-close-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      closeModal(this.dataset.closeModal);
    });
  });
}

function initMobileNav() {
  var currentPath = window.location.pathname;

  document.querySelectorAll('.bottom-nav a, .nav-item').forEach(function (link) {
    var href = link.getAttribute('href');
    if (!href) return;

    var normalizedHref = href.replace(/^\.\./, '').replace(/^\./, '');
    if (currentPath.endsWith(normalizedHref)) {
      link.classList.add('active');
    }
  });
}

function formatNumber(num) {
  return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

document.addEventListener('DOMContentLoaded', function () {
  initCountUpElements();
  initScrollAnimations();
  initModals();
  initMobileNav();
});
