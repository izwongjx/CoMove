/* ============================================
   ECORIDE - Driver Pages JavaScript
   My Rides data-driven rendering and modal behavior
   ============================================ */

(function () {
  'use strict';

  var pageRoot = document.getElementById('myRidesPage');
  if (!pageRoot) {
    return;
  }

  var iconBasePath = '../../public-assets/icons';

  /*
    Data contract injected by backend/PHP:
    window.driverRideData = {
      rides: [{ id, from, to, time, price, seats, booked, vehicle, status }],
      ridersByRide: {
        '<rideId>': [{ id, name, rating, seats, paymentMethod, phone, email, avatar }]
      },
      onCancelRide: function (rideId, ride) {}
    };
  */
  var dataSource = window.driverRideData;
  if (!dataSource || typeof dataSource !== 'object') {
    dataSource = {};
  }

  var rides = Array.isArray(dataSource.rides) ? dataSource.rides.slice() : [];
  var ridersByRide =
    dataSource.ridersByRide && typeof dataSource.ridersByRide === 'object'
      ? dataSource.ridersByRide
      : {};

  var onCancelRide =
    typeof dataSource.onCancelRide === 'function'
      ? dataSource.onCancelRide
      : typeof window.onDriverRideCancel === 'function'
      ? window.onDriverRideCancel
      : null;

  var ridesListElement = document.getElementById('myRidesList');
  var ridesEmptyStateElement = document.getElementById('myRidesEmptyState');

  var modalElement = document.getElementById('manageRideModal');
  var manageRideTimeElement = document.getElementById('manageRideTime');
  var manageRideStatusElement = document.getElementById('manageRideStatus');
  var manageRideVehicleElement = document.getElementById('manageRideVehicle');
  var manageRideFromElement = document.getElementById('manageRideFrom');
  var manageRideToElement = document.getElementById('manageRideTo');
  var manageRideSeatsElement = document.getElementById('manageRideSeats');
  var manageRidePriceElement = document.getElementById('manageRidePrice');
  var manageRidePassengersTitleElement = document.getElementById(
    'manageRidePassengersTitle'
  );
  var manageRideRidersElement = document.getElementById('manageRideRiders');
  var manageRideRidersEmptyElement = document.getElementById('manageRideRidersEmpty');

  var cancelRideTriggerButton = document.getElementById('cancelRideTrigger');
  var cancelRideConfirmElement = document.getElementById('cancelRideConfirm');
  var cancelRideConfirmTextElement = document.getElementById('cancelRideConfirmText');
  var cancelRideKeepButton = document.getElementById('cancelRideKeep');
  var cancelRideConfirmButton = document.getElementById('cancelRideConfirmButton');

  if (!ridesListElement || !modalElement) {
    return;
  }

  var selectedRide = null;
  var selectedRiders = [];
  var isCancelConfirmVisible = false;

  function escapeHtml(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function asText(value, fallback) {
    if (value === null || value === undefined || value === '') {
      return fallback;
    }
    return String(value);
  }

  function asNumber(value, fallback) {
    var parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
  }

  function formatMoney(value) {
    var numeric = asNumber(value, 0);
    return '$' + numeric.toFixed(2);
  }

  function toStatusClass(statusText) {
    var normalized = asText(statusText, '').toLowerCase().trim();

    if (normalized === 'scheduled') {
      return 'status-scheduled';
    }

    if (normalized === 'completed') {
      return 'status-completed';
    }

    if (normalized === 'cancelled' || normalized === 'canceled') {
      return 'status-cancelled';
    }

    if (
      normalized === 'in progress' ||
      normalized === 'in-progress' ||
      normalized === 'ongoing'
    ) {
      return 'status-progress';
    }

    return 'status-default';
  }

  function getRideById(rideId) {
    var rideIdString = String(rideId);
    return rides.find(function (ride) {
      return String(ride.id) === rideIdString;
    });
  }

  function getRidersForRide(ride) {
    if (!ride) {
      return [];
    }

    if (Array.isArray(ride.riders)) {
      return ride.riders;
    }

    var numericKey = ride.id;
    var stringKey = String(ride.id);
    var byNumericKey = ridersByRide[numericKey];
    var byStringKey = ridersByRide[stringKey];

    if (Array.isArray(byNumericKey)) {
      return byNumericKey;
    }

    if (Array.isArray(byStringKey)) {
      return byStringKey;
    }

    return [];
  }

  function getInitials(name) {
    var safeName = asText(name, 'Unknown Rider').trim();
    var parts = safeName.split(/\s+/).filter(Boolean);

    if (!parts.length) {
      return 'UR';
    }

    if (parts.length === 1) {
      return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function getPaymentConfig(method) {
    var normalized = asText(method, '').toLowerCase();
    if (normalized === 'cash') {
      return {
        badgeClass: 'cash',
        iconName: 'wallet.svg',
        label: 'Cash',
      };
    }

    return {
      badgeClass: 'card',
      iconName: 'credit-card.svg',
      label: 'Card',
    };
  }

  function buildRideCardMarkup(ride) {
    var statusText = asText(ride.status, 'Pending');
    var statusClass = toStatusClass(statusText);

    var booked = asNumber(ride.booked, 0);
    var seats = asNumber(ride.seats, 0);
    var price = asNumber(ride.price, 0);

    return (
      '<article class="ride-card">' +
      '  <div class="ride-card-header">' +
      '    <div class="ride-card-header-left">' +
      '      <span class="ride-status-badge ' +
      statusClass +
      '">' +
      escapeHtml(statusText) +
      '</span>' +
      '      <span class="ride-time">' +
      '        <img src="' +
      iconBasePath +
      '/calendar.svg" width="14" height="14" alt="" aria-hidden="true" />' +
      escapeHtml(asText(ride.time, 'Time not set')) +
      '      </span>' +
      '    </div>' +
      '    <span class="ride-vehicle">' +
      escapeHtml(asText(ride.vehicle, 'Vehicle not set')) +
      '</span>' +
      '  </div>' +
      '  <p class="ride-route">' +
      '    <span>' +
      escapeHtml(asText(ride.from, 'Pickup not set')) +
      '</span>' +
      '    <span class="ride-route-divider"></span>' +
      '    <span>' +
      escapeHtml(asText(ride.to, 'Dropoff not set')) +
      '</span>' +
      '  </p>' +
      '  <div class="ride-meta">' +
      '    <span class="ride-meta-item">' +
      '      <img src="' +
      iconBasePath +
      '/users.svg" width="14" height="14" alt="" aria-hidden="true" />' +
      '      <span><strong>' +
      booked +
      '</strong>/' +
      seats +
      ' Booked</span>' +
      '    </span>' +
      '    <span class="ride-meta-item">' +
      '      <img src="' +
      iconBasePath +
      '/dollar-sign.svg" width="14" height="14" alt="" aria-hidden="true" />' +
      '      <span><strong>' +
      formatMoney(price) +
      '</strong> per person</span>' +
      '    </span>' +
      '  </div>' +
      '  <div class="ride-actions">' +
      '    <button class="driver-btn driver-btn-primary driver-btn-small manage-bookings-btn" type="button" data-ride-id="' +
      escapeHtml(asText(ride.id, '')) +
      '">Manage Bookings</button>' +
      '  </div>' +
      '</article>'
    );
  }

  function buildRiderMarkup(rider) {
    var paymentConfig = getPaymentConfig(rider.paymentMethod);
    var rating = asNumber(rider.rating, 0).toFixed(1);
    var seats = asNumber(rider.seats, 1);
    var avatar = asText(rider.avatar, '');
    var avatarHtml;

    if (avatar) {
      avatarHtml =
        '<img src="' +
        escapeHtml(avatar) +
        '" alt="' +
        escapeHtml(asText(rider.name, 'Rider')) +
        '" />';
    } else {
      avatarHtml = escapeHtml(getInitials(rider.name));
    }

    return (
      '<article class="rider-card">' +
      '  <div class="rider-card-head">' +
      '    <div class="rider-identity">' +
      '      <div class="rider-avatar">' +
      avatarHtml +
      '</div>' +
      '      <div>' +
      '        <p class="rider-name">' +
      escapeHtml(asText(rider.name, 'Unknown Rider')) +
      '</p>' +
      '        <p class="rider-meta">' +
      '          <img class="rider-rating-icon" src="' +
      iconBasePath +
      '/star-filled.svg" width="12" height="12" alt="" aria-hidden="true" />' +
      '          <strong>' +
      rating +
      '</strong>' +
      '          <span>&middot; ' +
      seats +
      ' seat' +
      (seats === 1 ? '' : 's') +
      '</span>' +
      '        </p>' +
      '      </div>' +
      '    </div>' +
      '    <div class="rider-payment">' +
      '      <span class="payment-badge ' +
      paymentConfig.badgeClass +
      '">' +
      '        <img src="' +
      iconBasePath +
      '/' +
      paymentConfig.iconName +
      '" width="12" height="12" alt="" aria-hidden="true" />' +
      paymentConfig.label +
      '      </span>' +
      '    </div>' +
      '  </div>' +
      '  <div class="rider-contact-grid">' +
      '    <p class="rider-contact-item">' +
      '      <img src="' +
      iconBasePath +
      '/phone.svg" width="12" height="12" alt="" aria-hidden="true" />' +
      '      <span>' +
      escapeHtml(asText(rider.phone, '-')) +
      '</span>' +
      '    </p>' +
      '    <p class="rider-contact-item">' +
      '      <img src="' +
      iconBasePath +
      '/mail.svg" width="12" height="12" alt="" aria-hidden="true" />' +
      '      <span>' +
      escapeHtml(asText(rider.email, '-')) +
      '</span>' +
      '    </p>' +
      '  </div>' +
      '  <div class="rider-profile-wrap">' +
      '    <button class="rider-profile-btn" type="button">' +
      '      <img src="' +
      iconBasePath +
      '/user.svg" width="12" height="12" alt="" aria-hidden="true" />' +
      '      View Profile' +
      '    </button>' +
      '  </div>' +
      '</article>'
    );
  }

  function updateStatusBadge(element, statusText) {
    if (!element) {
      return;
    }

    element.classList.remove(
      'status-scheduled',
      'status-completed',
      'status-cancelled',
      'status-progress',
      'status-default'
    );

    var statusClass = toStatusClass(statusText);
    element.classList.add(statusClass);
    element.textContent = asText(statusText, 'Pending');
  }

  function renderRideCards() {
    if (!rides.length) {
      ridesListElement.innerHTML = '';
      if (ridesEmptyStateElement) {
        ridesEmptyStateElement.hidden = false;
      }
      return;
    }

    if (ridesEmptyStateElement) {
      ridesEmptyStateElement.hidden = true;
    }

    ridesListElement.innerHTML = rides.map(buildRideCardMarkup).join('');
  }

  function renderRidersList() {
    if (!manageRideRidersElement || !manageRideRidersEmptyElement) {
      return;
    }

    if (!selectedRiders.length) {
      manageRideRidersElement.innerHTML = '';
      manageRideRidersEmptyElement.hidden = false;
      return;
    }

    manageRideRidersEmptyElement.hidden = true;
    manageRideRidersElement.innerHTML = selectedRiders.map(buildRiderMarkup).join('');
  }

  function updateCancelConfirmationVisibility() {
    if (!cancelRideTriggerButton || !cancelRideConfirmElement) {
      return;
    }

    cancelRideTriggerButton.hidden = isCancelConfirmVisible;
    cancelRideConfirmElement.hidden = !isCancelConfirmVisible;
  }

  function setCancelConfirmation(show) {
    isCancelConfirmVisible = Boolean(show);
    updateCancelConfirmationVisibility();
  }

  function renderManageRideModal() {
    if (!selectedRide) {
      return;
    }

    var booked = asNumber(selectedRide.booked, 0);
    var seats = asNumber(selectedRide.seats, 0);
    var passengerCount = selectedRiders.length;

    if (manageRideTimeElement) {
      manageRideTimeElement.textContent = asText(selectedRide.time, 'Time not set');
    }

    updateStatusBadge(manageRideStatusElement, selectedRide.status);

    if (manageRideVehicleElement) {
      manageRideVehicleElement.textContent = asText(selectedRide.vehicle, 'Vehicle not set');
    }

    if (manageRideFromElement) {
      manageRideFromElement.textContent = asText(selectedRide.from, 'Pickup not set');
    }

    if (manageRideToElement) {
      manageRideToElement.textContent = asText(selectedRide.to, 'Dropoff not set');
    }

    if (manageRideSeatsElement) {
      manageRideSeatsElement.innerHTML =
        '<strong>' + booked + '</strong>/' + seats + ' Booked';
    }

    if (manageRidePriceElement) {
      manageRidePriceElement.innerHTML =
        '<strong>' + formatMoney(selectedRide.price) + '</strong> per person';
    }

    if (manageRidePassengersTitleElement) {
      manageRidePassengersTitleElement.textContent =
        'Passenger List (' + passengerCount + ')';
    }

    if (cancelRideConfirmTextElement) {
      cancelRideConfirmTextElement.textContent =
        'This will cancel the entire ride and this action cannot be undone.';
    }

    renderRidersList();
    updateCancelConfirmationVisibility();
  }

  function openManageRideModal(rideId) {
    var matchingRide = getRideById(rideId);
    if (!matchingRide) {
      return;
    }

    selectedRide = matchingRide;
    selectedRiders = getRidersForRide(matchingRide);
    setCancelConfirmation(false);
    renderManageRideModal();

    modalElement.classList.add('is-open');
    modalElement.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
  }

  function closeManageRideModal() {
    selectedRide = null;
    selectedRiders = [];
    setCancelConfirmation(false);

    modalElement.classList.remove('is-open');
    modalElement.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  }

  function handleCancelRide() {
    if (!selectedRide) {
      return;
    }

    var rideId = selectedRide.id;
    var shouldRemoveLocalRide = true;

    if (onCancelRide) {
      try {
        var callbackResult = onCancelRide(rideId, selectedRide);
        if (callbackResult === false) {
          shouldRemoveLocalRide = false;
        }
      } catch (error) {
        console.error('Cancel ride callback failed:', error);
      }
    }

    if (shouldRemoveLocalRide) {
      var removeIndex = rides.findIndex(function (ride) {
        return String(ride.id) === String(rideId);
      });

      if (removeIndex >= 0) {
        rides.splice(removeIndex, 1);
      }

      delete ridersByRide[rideId];
      delete ridersByRide[String(rideId)];

      renderRideCards();
    }

    closeManageRideModal();
  }

  ridesListElement.addEventListener('click', function (event) {
    var button = event.target.closest('.manage-bookings-btn');
    if (!button) {
      return;
    }

    openManageRideModal(button.getAttribute('data-ride-id'));
  });

  var closeModalElements = document.querySelectorAll('[data-close-manage-modal]');
  closeModalElements.forEach(function (closeElement) {
    closeElement.addEventListener('click', function () {
      closeManageRideModal();
    });
  });

  if (cancelRideTriggerButton) {
    cancelRideTriggerButton.addEventListener('click', function () {
      setCancelConfirmation(true);
    });
  }

  if (cancelRideKeepButton) {
    cancelRideKeepButton.addEventListener('click', function () {
      setCancelConfirmation(false);
    });
  }

  if (cancelRideConfirmButton) {
    cancelRideConfirmButton.addEventListener('click', function () {
      handleCancelRide();
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && modalElement.classList.contains('is-open')) {
      closeManageRideModal();
    }
  });

  window.openDriverManageRideModal = openManageRideModal;

  renderRideCards();
})();
