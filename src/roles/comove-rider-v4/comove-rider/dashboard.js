/* Comove – Rider Dashboard JS */
var dbSelectedPayment = 'tng';
var dbCurrentRide = {};

function initRiderDashboard() {
  var el = document.getElementById('dashDate');
  if (el) {
    var now = new Date();
    var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    el.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear() + ' · Rider Account';
  }
  loadDashboard();
}

async function loadDashboard() {
  try {
    var data = await apiGet('api/dashboard.php');
    renderDashboard(data);
  } catch (err) {
    showToast('⚠️ Unable to load dashboard');
  }
}

function renderDashboard(data) {
  document.getElementById('riderName').textContent = data.name || 'Rider';
  document.getElementById('dashPoints').textContent = data.green_points || 0;
  document.getElementById('dashTrips').textContent = data.total_trips || 0;

  document.getElementById('dashAvailableRides').innerHTML = (data.available_rides || []).map(function(ride) {
    return '<div class="dash-ride-card">'
      + '<div class="driver-ava" style="flex-shrink:0;">' + escapeHtml(ride.driver_initials) + '</div>'
      + '<div class="ride-details" style="flex:1;">'
      + '<div class="ride-driver-name">' + escapeHtml(ride.driver_name) + '</div>'
      + '<div class="ride-info">' + escapeHtml(ride.from) + ' → ' + escapeHtml(ride.to) + ' · ' + escapeHtml(ride.departure_time) + ' · ' + ride.seats_left + ' seats left <span class="ride-pts-tag">+' + ride.points + ' pts</span></div>'
      + '<div style="font-size:12px;color:var(--gray-400);">' + escapeHtml(ride.vehicle_model) + ' · ' + escapeHtml(ride.plate_number) + '</div>'
      + '</div>'
      + '<div style="text-align:right;flex-shrink:0;">'
      + '<div class="ride-price">' + escapeHtml(ride.price) + '</div>'
      + '<button class="btn-sm primary" style="margin-top:6px;" onclick="dashBook(' + ride.trip_id + ')">Book</button>'
      + '</div>'
      + '</div>';
  }).join('') || '<div class="form-card">No rides available right now.</div>';

  document.getElementById('dashRecentTrips').innerHTML = (data.recent_trips || []).map(function(trip) {
    return '<a href="my-trips.html" class="trip-card">'
      + '<div class="trip-icon">🚗</div>'
      + '<div class="trip-info"><div class="trip-route">' + escapeHtml(trip.route) + '</div><div class="trip-meta">' + escapeHtml(trip.meta) + '</div></div>'
      + '<div class="trip-pts">+' + trip.points + ' pts</div>'
      + '</a>';
  }).join('') || '<div class="form-card">No recent trips yet.</div>';

  window.dashboardAvailableRides = data.available_rides || [];
}

function dashBook(tripId) {
  var rides = window.dashboardAvailableRides || [];
  var ride = rides.find(function(item) { return item.trip_id === tripId; });
  if (!ride) return;

  dbCurrentRide = ride;
  dbCurrentRide.selected_seats = 1;
  document.getElementById('dbAva').textContent = ride.driver_initials;
  document.getElementById('dbName').textContent = ride.driver_name;
  document.getElementById('dbCarPlate').textContent = ride.vehicle_model + ' · ' + ride.plate_number;
  document.getElementById('dbFrom').textContent = ride.from;
  document.getElementById('dbTo').textContent = ride.to;
  document.getElementById('dbTime').textContent = ride.departure_time;
  populateSeatOptions(ride.seats_left);
  updateDashboardBookingPrice();
  dbSelectPayment('tng');
  document.getElementById('dashBookModal').classList.add('open');
}

function populateSeatOptions(maxSeats) {
  var select = document.getElementById('dbSeatCount');
  if (!select) return;

  var count = Math.max(1, parseInt(maxSeats, 10) || 1);
  var options = '';
  for (var i = 1; i <= count; i++) {
    options += '<option value="' + i + '">' + i + ' seat' + (i > 1 ? 's' : '') + '</option>';
  }
  select.innerHTML = options;
  select.value = '1';
}

function updateDashboardBookingPrice() {
  var select = document.getElementById('dbSeatCount');
  var seatCount = select ? parseInt(select.value, 10) || 1 : 1;
  dbCurrentRide.selected_seats = seatCount;

  var unitPrice = Number(dbCurrentRide.unit_price || 0);
  var totalPrice = unitPrice * seatCount;
  document.getElementById('dbPrice').textContent = 'RM ' + totalPrice.toFixed(2);
}

function closeDashBookModal() {
  document.getElementById('dashBookModal').classList.remove('open');
}

function dbSelectPayment(method) {
  dbSelectedPayment = method;
  ['tng','cash','grab','bank'].forEach(function(m) {
    document.getElementById('db-pay-' + m).classList.toggle('selected', m === method);
  });
}

async function dbConfirmBooking() {
  closeDashBookModal();
  var labels = { tng: "Touch 'n Go", cash: 'Cash', grab: 'GrabPay', bank: 'Online Banking' };
  var formData = new FormData();
  formData.append('trip_id', dbCurrentRide.trip_id);
  formData.append('payment_method', labels[dbSelectedPayment]);
  formData.append('seats_requested', String(dbCurrentRide.selected_seats || 1));

  try {
    var result = await apiPost('api/book-ride.php', formData);
    showReceipt(dbCurrentRide, labels[dbSelectedPayment], result.reference, result.amount_paid);
    loadDashboard();
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

function showReceipt(ride, payMethod, ref, amountPaid) {
  var now = new Date();
  var timeStr = now.toLocaleTimeString('en-MY', { hour:'2-digit', minute:'2-digit' });
  var dateStr = now.toLocaleDateString('en-MY', { day:'numeric', month:'long', year:'numeric' });
  document.getElementById('receiptContent').innerHTML =
    '<div class="receipt-card">'
    + '<div class="receipt-stamp">✅</div>'
    + '<div class="receipt-title">Payment Confirmed</div>'
    + '<div class="receipt-sub">Your ride has been booked successfully</div>'
    + '<div class="receipt-row"><span class="lbl">Driver</span><span class="val">' + escapeHtml(ride.driver_name) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Vehicle</span><span class="val">' + escapeHtml(ride.vehicle_model) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Plate No.</span><span class="val" style="color:var(--lime);font-family:monospace;">' + escapeHtml(ride.plate_number) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">From</span><span class="val">' + escapeHtml(ride.from) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">To</span><span class="val">' + escapeHtml(ride.to) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Departure</span><span class="val">' + escapeHtml(ride.departure_time) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Seats</span><span class="val">' + (ride.selected_seats || 1) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Amount Paid</span><span class="val" style="color:var(--lime);">' + escapeHtml(amountPaid || ride.price) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Payment</span><span class="val">' + escapeHtml(payMethod) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Date & Time</span><span class="val">' + dateStr + ' ' + timeStr + '</span></div>'
    + '<div class="receipt-row" style="border-bottom:none;"><span class="lbl">Green Points</span><span class="val" style="color:var(--lime);">Saved to database</span></div>'
    + '<div class="receipt-ref">Reference: <span>' + escapeHtml(ref) + '</span></div>'
    + '</div>'
    + '<button class="btn-primary" style="width:100%;justify-content:center;" onclick="closeReceipt()">Done</button>';
  document.getElementById('receiptModal').classList.add('open');
}

function closeReceipt() {
  document.getElementById('receiptModal').classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function() {
  ['dashBookModal','receiptModal'].forEach(function(id) {
    var m = document.getElementById(id);
    if (m) m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
  });
  initRiderDashboard();
});
