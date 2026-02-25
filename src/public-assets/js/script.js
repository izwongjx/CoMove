/* ============================================
   ECORIDE - Shared JavaScript Utilities
   Pure Vanilla JS - No Frameworks
   ============================================ */

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
  initModals();
  initMobileNav();
});
