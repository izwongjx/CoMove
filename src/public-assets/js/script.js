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

function initTabs(containerSelector) {
  var container = document.querySelector(containerSelector);
  if (!container) return;

  var buttons = container.querySelectorAll('[data-tab-btn]');
  var contents = container.querySelectorAll('[data-tab-content]');

  buttons.forEach(function (btn) {
    btn.addEventListener(
      'click',
      function () {
        var tabId = this.dataset.tabBtn;

        buttons.forEach(function (b) {
          b.classList.remove('active');
        });
        this.classList.add('active');

        contents.forEach(function (c) {
          c.classList.remove('active');
          if (c.dataset.tabContent === tabId) {
            c.classList.add('active');
          }
        });
      }.bind(btn)
    );
  });
}

function openModal(modalId) {
  var modal = document.getElementById(modalId);
  if (!modal) return;

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
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

function resolveIconBaseUrl() {
  var scriptSrc = '';
  var marker = 'public-assets/js/script.js';

  if (document.currentScript && document.currentScript.getAttribute('src')) {
    scriptSrc = document.currentScript.getAttribute('src');
  }

  if (!scriptSrc) {
    var scripts = document.getElementsByTagName('script');
    for (var i = scripts.length - 1; i >= 0; i--) {
      var candidate = scripts[i].getAttribute('src') || '';
      if (candidate.indexOf(marker) !== -1) {
        scriptSrc = candidate;
        break;
      }
    }
  }

  try {
    var scriptUrl = new URL(scriptSrc || 'src/public-assets/js/script.js', window.location.href);
    return new URL('../images/icons/', scriptUrl).href;
  } catch (err) {
    return 'src/public-assets/images/icons/';
  }
}

var ICON_BASE_URL = resolveIconBaseUrl();
var ICON_TEXT_CACHE = {};

function fetchIconText(name) {
  if (!ICON_TEXT_CACHE[name]) {
    var iconUrl = ICON_BASE_URL + encodeURIComponent(name) + '.svg';
    ICON_TEXT_CACHE[name] = fetch(iconUrl).then(function (response) {
      if (!response.ok) {
        throw new Error('Icon not found: ' + name);
      }
      return response.text();
    });
  }

  return ICON_TEXT_CACHE[name];
}

function createIconFromSvgText(svgText, size, className) {
  var wrapper = document.createElement('div');
  wrapper.innerHTML = svgText.trim();

  var svg = wrapper.querySelector('svg');
  if (!svg) return null;

  svg.setAttribute('width', size);
  svg.setAttribute('height', size);

  if (className) {
    svg.setAttribute('class', className);
  }

  svg.style.display = 'inline-block';
  svg.style.verticalAlign = 'middle';
  svg.style.flexShrink = '0';

  return svg;
}

function initIcons() {
  var placeholders = document.querySelectorAll('[data-icon]');
  if (!placeholders.length) return Promise.resolve();

  var tasks = [];

  placeholders.forEach(function (el) {
    var name = el.dataset.icon;
    var size = parseInt(el.dataset.iconSize, 10) || 20;
    var className = el.className;

    var task = fetchIconText(name)
      .then(function (svgText) {
        var icon = createIconFromSvgText(svgText, size, className);
        if (!icon || !el.parentNode) return;

        el.parentNode.replaceChild(icon, el);
      })
      .catch(function () {
        el.setAttribute('data-icon-missing', name);
      });

    tasks.push(task);
  });

  return Promise.all(tasks);
}

document.addEventListener('DOMContentLoaded', function () {
  initIcons();
  initCountUpElements();
  initScrollAnimations();
  initModals();
  initMobileNav();
});
