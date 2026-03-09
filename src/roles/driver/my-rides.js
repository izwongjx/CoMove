/* ============================================
   ECORIDE - Driver My Rides (Table Overview)
   ============================================ */

function initDriverMyRidesPage() {
  var pageRoot = document.getElementById('myRidesPage');
  var ridesListElement = document.getElementById('myRidesList');
  var ridesEmptyStateElement = document.getElementById('myRidesEmptyState');

  if (!pageRoot || !ridesListElement) {
    return;
  }

  /*
    Replace this hardcoded block with PHP output later.
    Example:
    while (...) { echo "..."; }
  */
  if (!window.driverRideData || typeof window.driverRideData !== 'object') {
    window.driverRideData = {
      rides: [
        {
          id: 101,
          from: 'University Campus',
          to: 'Downtown Mall',
          time: '2026-03-10 08:30:00',
          estimatedDuration: '35 mins',
          totalPrice: 44,
          seats: 4,
          booked: 2,
          vehicle: 'Toyota Vios',
        },
        {
          id: 102,
          from: 'APU Residence',
          to: 'KL Sentral',
          time: '2026-03-10 18:15:00',
          estimatedDuration: '45 mins',
          totalPrice: 108,
          seats: 6,
          booked: 6,
          vehicle: 'Perodua Alza',
        },
        {
          id: 103,
          from: 'Bukit Jalil',
          to: 'Sunway Pyramid',
          time: '2026-03-11 09:00:00',
          estimatedDuration: '25 mins',
          totalPrice: 12,
          seats: 4,
          booked: 1,
          vehicle: 'Honda City',
        },
      ],
      ridersByRide: {
        101: [
          {
            name: 'Alicia Tan',
            seats: 1,
            paymentMethod: 'Cash',
            phone: '+65 9123 4567',
            email: 'alicia.tan@mail.com',
          },
          {
            name: 'Marcus Lim',
            seats: 1,
            paymentMethod: 'Card',
            phone: '+65 9345 6789',
            email: 'marcus.lim@mail.com',
          },
        ],
        102: [
          {
            name: 'Jason Lee',
            seats: 1,
            paymentMethod: 'Card',
            phone: '+65 9001 1111',
            email: 'jason.lee@mail.com',
          },
          {
            name: 'Nur Aina',
            seats: 1,
            paymentMethod: 'Cash',
            phone: '+65 9001 2222',
            email: 'nur.aina@mail.com',
          },
          {
            name: 'Kumar Raj',
            seats: 1,
            paymentMethod: 'Card',
            phone: '+65 9001 3333',
            email: 'kumar.raj@mail.com',
          },
          {
            name: 'Mei Xin',
            seats: 1,
            paymentMethod: 'Cash',
            phone: '+65 9001 4444',
            email: 'mei.xin@mail.com',
          },
          {
            name: 'Adam Wong',
            seats: 1,
            paymentMethod: 'Card',
            phone: '+65 9001 5555',
            email: 'adam.wong@mail.com',
          },
          {
            name: 'Siti Zara',
            seats: 1,
            paymentMethod: 'Cash',
            phone: '+65 9001 6666',
            email: 'siti.zara@mail.com',
          },
        ],
        103: [
          {
            name: 'Farah Nabilah',
            seats: 1,
            paymentMethod: 'Card',
            phone: '+65 9111 0000',
            email: 'farah.n@mail.com',
          },
        ],
      },
    };
  }

  var dataSource = window.driverRideData;
  var rides = Array.isArray(dataSource.rides) ? dataSource.rides.slice() : [];
  var ridersByRide =
    dataSource.ridersByRide && typeof dataSource.ridersByRide === 'object'
      ? dataSource.ridersByRide
      : {};

  var selectedRideId = null;

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

  function getRideById(rideId) {
    return rides.find(function (ride) {
      return String(ride.id) === String(rideId);
    });
  }

  function getRidersForRide(rideId) {
    var numericKey = Number(rideId);
    if (Array.isArray(ridersByRide[rideId])) {
      return ridersByRide[rideId];
    }
    if (Array.isArray(ridersByRide[numericKey])) {
      return ridersByRide[numericKey];
    }
    return [];
  }

  function getAvailableSeats(ride) {
    var seats = asNumber(ride.seats, 0);
    var booked = asNumber(ride.booked, 0);
    return Math.max(seats - booked, 0);
  }

  function getInitials(name) {
    var safeName = asText(name, 'Unknown');
    var parts = safeName.trim().split(/\s+/).filter(Boolean);

    if (!parts.length) {
      return 'NA';
    }

    if (parts.length === 1) {
      return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function buildOverviewTableMarkup() {
    var bodyRows = rides
      .map(function (ride) {
        var availableSeats = getAvailableSeats(ride);
        var totalSeats = asNumber(ride.seats, 0);

        return (
          '<tr>' +
          '  <td data-label="Date Time">' +
          escapeHtml(asText(ride.time, '-')) +
          '</td>' +
          '  <td data-label="Location">' +
          escapeHtml(asText(ride.from, '-')) +
          ' -> ' +
          escapeHtml(asText(ride.to, '-')) +
          '</td>' +
          '  <td data-label="Estimated Duration">' +
          escapeHtml(asText(ride.estimatedDuration, '-')) +
          '</td>' +
          '  <td data-label="Total Price">' +
          escapeHtml(formatMoney(ride.totalPrice)) +
          '</td>' +
          '  <td data-label="Available Place">' +
          availableSeats +
          '/' +
          totalSeats +
          '</td>' +
          '  <td data-label="Action" class="rides-action-cell">' +
          '    <button type="button" class="driver-btn driver-btn-small driver-btn-muted view-ride-btn" data-ride-id="' +
          escapeHtml(asText(ride.id, '')) +
          '">' +
          'View' +
          '</button>' +
          '    <button type="button" class="driver-btn driver-btn-small driver-btn-danger delete-ride-btn" data-ride-id="' +
          escapeHtml(asText(ride.id, '')) +
          '">' +
          'Delete' +
          '</button>' +
          '  </td>' +
          '</tr>'
        );
      })
      .join('');

    return (
      '<div class="my-rides-table-wrap">' +
      '  <table class="my-rides-table">' +
      '    <thead>' +
      '      <tr>' +
      '        <th>Date Time</th>' +
      '        <th>Location</th>' +
      '        <th>Estimated Duration</th>' +
      '        <th>Total Price</th>' +
      '        <th>Available Place</th>' +
      '        <th>Action</th>' +
      '      </tr>' +
      '    </thead>' +
      '    <tbody>' +
      bodyRows +
      '    </tbody>' +
      '  </table>' +
      '</div>'
    );
  }

  function buildTripDetailMarkup(ride) {
    var availableSeats = getAvailableSeats(ride);
    var totalSeats = asNumber(ride.seats, 0);

    return (
      '<div class="ride-detail-block">' +
      '  <h3 class="ride-detail-title">Trip Details</h3>' +
      '  <table class="ride-detail-table">' +
      '    <tbody>' +
      '      <tr><th>Date Time</th><td>' +
      escapeHtml(asText(ride.time, '-')) +
      '</td></tr>' +
      '      <tr><th>Pickup</th><td>' +
      escapeHtml(asText(ride.from, '-')) +
      '</td></tr>' +
      '      <tr><th>Dropoff</th><td>' +
      escapeHtml(asText(ride.to, '-')) +
      '</td></tr>' +
      '      <tr><th>Estimated Duration</th><td>' +
      escapeHtml(asText(ride.estimatedDuration, '-')) +
      '</td></tr>' +
      '      <tr><th>Total Price</th><td>' +
      escapeHtml(formatMoney(ride.totalPrice)) +
      '</td></tr>' +
      '      <tr><th>Available Place</th><td>' +
      availableSeats +
      '/' +
      totalSeats +
      '</td></tr>' +
      '    </tbody>' +
      '  </table>' +
      '</div>'
    );
  }

  function buildPassengerRowsMarkup(passengers) {
    if (!passengers.length) {
      return '<tr><td colspan="6" class="passenger-empty">No passengers yet.</td></tr>';
    }

    return passengers
      .map(function (rider) {
        return (
          '<tr>' +
          '  <td data-label="Profile"><span class="profile-chip">' +
          escapeHtml(getInitials(rider.name)) +
          '</span></td>' +
          '  <td data-label="Name">' +
          escapeHtml(asText(rider.name, '-')) +
          '</td>' +
          '  <td data-label="Requested Seat">' +
          asNumber(rider.seats, 1) +
          '</td>' +
          '  <td data-label="Phone Number">' +
          escapeHtml(asText(rider.phone, '-')) +
          '</td>' +
          '  <td data-label="Email">' +
          escapeHtml(asText(rider.email, '-')) +
          '</td>' +
          '  <td data-label="Payment Method">' +
          escapeHtml(asText(rider.paymentMethod, 'Cash')) +
          '</td>' +
          '</tr>'
        );
      })
      .join('');
  }

  function buildDetailSectionMarkup() {
    var selectedRide = getRideById(selectedRideId);
    if (!selectedRide) {
      selectedRideId = null;
      return '';
    }

    var passengers = getRidersForRide(selectedRide.id);

    return (
      '<div class="ride-detail-view">' +
      '  <div class="ride-detail-top-actions">' +
      '    <button type="button" class="driver-btn driver-btn-small driver-btn-muted back-overview-btn">Back</button>' +
      '  </div>' +
      '  <section class="ride-detail-panel">' +
      buildTripDetailMarkup(selectedRide) +
      '    <div class="ride-detail-block">' +
      '      <h3 class="ride-detail-title">Passenger List</h3>' +
      '      <div class="passenger-table-wrap">' +
      '        <table class="passenger-table">' +
      '          <thead>' +
      '            <tr>' +
      '              <th>Profile</th>' +
      '              <th>Name</th>' +
      '              <th>Requested Seat</th>' +
      '              <th>Phone Number</th>' +
      '              <th>Email</th>' +
      '              <th>Payment Method</th>' +
      '            </tr>' +
      '          </thead>' +
      '          <tbody>' +
      buildPassengerRowsMarkup(passengers) +
      '          </tbody>' +
      '        </table>' +
      '      </div>' +
      '    </div>' +
      '  </section>' +
      '</div>'
    );
  }

  function renderPage() {
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

    if (selectedRideId) {
      var detailMarkup = buildDetailSectionMarkup();
      ridesListElement.innerHTML = detailMarkup || buildOverviewTableMarkup();
      return;
    }

    ridesListElement.innerHTML = buildOverviewTableMarkup();
  }

  function deleteRide(rideId) {
    var rideIndex = rides.findIndex(function (ride) {
      return String(ride.id) === String(rideId);
    });
    if (rideIndex < 0) {
      return;
    }

    var removedRide = rides[rideIndex];
    rides.splice(rideIndex, 1);
    delete ridersByRide[removedRide.id];
    delete ridersByRide[String(removedRide.id)];

    if (String(selectedRideId) === String(removedRide.id)) {
      selectedRideId = null;
    }

    renderPage();
  }

  ridesListElement.addEventListener('click', function (event) {
    var backButton = event.target.closest('.back-overview-btn');
    if (backButton) {
      selectedRideId = null;
      renderPage();
      return;
    }

    var viewButton = event.target.closest('.view-ride-btn');
    if (viewButton) {
      selectedRideId = viewButton.getAttribute('data-ride-id');
      renderPage();
      return;
    }

    var deleteButton = event.target.closest('.delete-ride-btn');
    if (deleteButton) {
      deleteRide(deleteButton.getAttribute('data-ride-id'));
    }
  });

  renderPage();
}

initDriverMyRidesPage();
