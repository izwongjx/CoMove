/* ============================================
   ECORIDE - Driver Pages JavaScript
   My Rides inline rendering
   ============================================ */

function initDriverMyRidesPage() {
  var pageRoot = document.getElementById('myRidesPage');
  if (!pageRoot) {
    return;
  }

  /* Replace this default data with PHP output from database. */
  window.driverRideData = window.driverRideData = {
    rides: [
      {
        id: 101,
        from: 'University Campus',
        to: 'Downtown Mall',
        time: '2026-03-10 08:30:00',
        price: 22,
        seats: 4,
        booked: 2,
        vehicle: 'Toyota Vios',
        status: 'Scheduled',
      },
      {
        id: 102,
        from: 'APU Residence',
        to: 'KL Sentral',
        time: '2026-03-10 18:15:00',
        price: 18,
        seats: 6,
        booked: 6,
        vehicle: 'Perodua Alza',
        status: 'In Progress',
      },
      {
        id: 103,
        from: 'Bukit Jalil',
        to: 'Sunway Pyramid',
        time: '2026-03-11 09:00:00',
        price: 12,
        seats: 4,
        booked: 1,
        vehicle: 'Honda City',
        status: 'Scheduled',
      },
    ],
    ridersByRide: {
      101: [
        { id: 'R-2001', name: 'Alicia Tan', rating: 4.8, seats: 1, paymentMethod: 'card', phone: '+65 9123 4567', email: 'alicia.tan@mail.com' },
        { id: 'R-2002', name: 'Marcus Lim', rating: 4.6, seats: 1, paymentMethod: 'cash', phone: '+65 9345 6789', email: 'marcus.lim@mail.com' },
      ],
      102: [
        { id: 'R-2101', name: 'Jason Lee', rating: 4.9, seats: 1, paymentMethod: 'card', phone: '+65 9001 1111', email: 'jason.lee@mail.com' },
        { id: 'R-2102', name: 'Nur Aina', rating: 4.7, seats: 1, paymentMethod: 'cash', phone: '+65 9001 2222', email: 'nur.aina@mail.com' },
        { id: 'R-2103', name: 'Kumar Raj', rating: 4.5, seats: 1, paymentMethod: 'card', phone: '+65 9001 3333', email: 'kumar.raj@mail.com' },
        { id: 'R-2104', name: 'Mei Xin', rating: 4.8, seats: 1, paymentMethod: 'cash', phone: '+65 9001 4444', email: 'mei.xin@mail.com' },
        { id: 'R-2105', name: 'Adam Wong', rating: 4.6, seats: 1, paymentMethod: 'card', phone: '+65 9001 5555', email: 'adam.wong@mail.com' },
        { id: 'R-2106', name: 'Siti Zara', rating: 4.9, seats: 1, paymentMethod: 'cash', phone: '+65 9001 6666', email: 'siti.zara@mail.com' },
      ],
      103: [
        { id: 'R-2201', name: 'Farah Nabilah', rating: 4.8, seats: 1, paymentMethod: 'card', phone: '+65 9111 0000', email: 'farah.n@mail.com' },
      ],
    },
  };


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
  if (!ridesListElement) {
    return;
  }

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

  function buildRidersListMarkup(riders) {
    if (!riders.length) {
      return (
        '<div class="manage-riders-empty">' +
        '  <img src="' +
        iconBasePath +
        '/users.svg" width="36" height="36" alt="" aria-hidden="true" />' +
        '  <p class="manage-riders-empty-title">No passengers yet.</p>' +
        '  <p class="manage-riders-empty-text">Bookings will appear here once accepted.</p>' +
        '</div>'
      );
    }

    return '<div class="manage-riders-list">' + riders.map(buildRiderMarkup).join('') + '</div>';
  }

  function buildRideCardMarkup(ride) {
    var statusText = asText(ride.status, 'Pending');
    var statusClass = toStatusClass(statusText);
    var booked = asNumber(ride.booked, 0);
    var seats = asNumber(ride.seats, 0);
    var price = asNumber(ride.price, 0);
    var riders = getRidersForRide(ride);

    return (
      '<article class="ride-card" data-ride-card-id="' +
      escapeHtml(asText(ride.id, '')) +
      '">' +
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
      '  <div class="ride-manage-panel">' +
      '    <section class="manage-riders-section">' +
      '      <h3 class="manage-riders-title">Passenger List (' +
      riders.length +
      ')</h3>' +
      buildRidersListMarkup(riders) +
      '    </section>' +
      '    <section class="manage-cancel-section">' +
      '      <button class="driver-btn driver-btn-danger-outline cancel-ride-btn" type="button" data-ride-id="' +
      escapeHtml(asText(ride.id, '')) +
      '">' +
      '        <img src="' +
      iconBasePath +
      '/alert-triangle.svg" width="16" height="16" alt="" aria-hidden="true" />' +
      '        Cancel Entire Ride' +
      '      </button>' +
      '    </section>' +
      '  </div>' +
      '</article>'
    );
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

  function handleCancelRide(rideId) {
    var selectedRide = getRideById(rideId);
    if (!selectedRide) {
      return;
    }

    var shouldRemoveLocalRide = true;
    if (onCancelRide) {
      try {
        var callbackResult = onCancelRide(selectedRide.id, selectedRide);
        if (callbackResult === false) {
          shouldRemoveLocalRide = false;
        }
      } catch (error) {
        console.error('Cancel ride callback failed:', error);
      }
    }

    if (!shouldRemoveLocalRide) {
      return;
    }

    var removeIndex = rides.findIndex(function (ride) {
      return String(ride.id) === String(selectedRide.id);
    });
    if (removeIndex >= 0) {
      rides.splice(removeIndex, 1);
    }

    delete ridersByRide[selectedRide.id];
    delete ridersByRide[String(selectedRide.id)];

    renderRideCards();
  }

  ridesListElement.addEventListener('click', function (event) {
    var cancelButton = event.target.closest('.cancel-ride-btn');
    if (!cancelButton) {
      return;
    }

    handleCancelRide(cancelButton.getAttribute('data-ride-id'));
  });

  /* Keep compatibility for pages/scripts still calling this old entry point. */
  window.openDriverManageRideModal = function (rideId) {
    var rideCard = ridesListElement.querySelector(
      '.ride-card[data-ride-card-id="' + String(rideId) + '"]'
    );

    if (rideCard && typeof rideCard.scrollIntoView === 'function') {
      rideCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  };

  renderRideCards();
}

initDriverMyRidesPage();
