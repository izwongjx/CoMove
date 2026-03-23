/* Comove – Find Rides JS */
var selectedPayment = 'tng';
var currentBooking = {};

function initRiderFindRides() {
  var now = new Date();
  var d = document.getElementById('dateInput');
  if (d) d.value = now.toISOString().split('T')[0];
}

function selectPill(el) {
  el.closest('.type-pills').querySelectorAll('.type-pill').forEach(function(p){ p.classList.remove('active'); });
  el.classList.add('active');
}

function getSelectedSeatCount() {
  var active = document.querySelector('.type-pills .type-pill.active');
  if (!active) return 1;
  var count = parseInt(active.textContent, 10);
  return count > 0 ? count : 1;
}

async function searchRides() {
  var pickup = document.getElementById('pickupInput').value.trim();
  var drop = document.getElementById('dropInput').value.trim();
  var seats = getSelectedSeatCount();
  if (!pickup || !drop) { showToast('⚠️ Please enter pickup and destination'); return; }

  try {
    var data = await apiGet('api/rides.php?pickup=' + encodeURIComponent(pickup) + '&drop=' + encodeURIComponent(drop) + '&seats=' + seats);
    renderRideResults(data.rides || []);
    document.getElementById('availableRides').style.display = 'block';
    document.getElementById('availableRides').scrollIntoView({ behavior: 'smooth' });
    showToast('✅ ' + (data.rides || []).length + ' rides found near you!');
  } catch (err) {
    showToast('⚠️ Unable to load rides');
  }
}

function renderRideResults(rides) {
  var list = document.getElementById('rideResultsList');
  if (!rides.length) {
    list.innerHTML = '<div class="form-card">No rides matched your route yet.</div>';
    return;
  }

  list.innerHTML = rides.map(function(ride) {
    return '<div class="ride-offer-card">'
      + '<div class="driver-ava"><img src="' + escapeHtml(ride.driver_photo_url) + '" alt="' + escapeHtml(ride.driver_name) + ' profile photo"></div>'
      + '<div class="ride-details">'
      + '<div class="ride-driver-name">' + escapeHtml(ride.driver_name) + '</div>'
      + '<div class="ride-info">' + escapeHtml(ride.time) + ' · ' + escapeHtml(ride.vehicle_model) + ' · ' + escapeHtml(ride.plate_number) + ' · ' + ride.seats_left + ' seats left <span class="ride-pts-tag">+' + ride.points + ' pts</span></div>'
      + '<div class="rating-stars">' + escapeHtml(ride.from) + ' → ' + escapeHtml(ride.to) + '</div>'
      + '</div>'
      + '<div style="text-align:right;">'
      + '<div class="ride-price">' + escapeHtml(ride.price) + '</div>'
      + '<button class="btn-sm primary" style="margin-top:6px;" onclick="goToBooking(' + ride.trip_id + ')">Book</button>'
      + '</div>'
      + '</div>';
  }).join('');

  window.riderSearchResults = rides;
}

function goToBooking(tripId) {
  var rides = window.riderSearchResults || [];
  var ride = rides.find(function(item) { return item.trip_id === tripId; });
  if (!ride) return;

  currentBooking = {
    trip_id: ride.trip_id,
    name: ride.driver_name,
    car: ride.vehicle_model,
    plate: ride.plate_number,
    price: ride.price,
    unit_price: ride.unit_price,
    points: ride.points,
    seats_left: ride.seats_left,
    time: ride.time,
    from: ride.from,
    to: ride.to,
    date: ride.date,
    photo_url: ride.driver_photo_url,
    seats_requested: getSelectedSeatCount()
  };

  document.getElementById('bkAva').innerHTML = '<img src="' + escapeHtml(ride.driver_photo_url) + '" alt="' + escapeHtml(ride.driver_name) + ' profile photo">';
  document.getElementById('bkName').textContent = ride.driver_name;
  document.getElementById('bkCar').textContent = ride.vehicle_model + ' · ' + ride.plate_number;
  document.getElementById('bkRating').textContent = ride.seats_left + ' seat' + (ride.seats_left > 1 ? 's' : '') + ' left';
  document.getElementById('bkPrice').textContent = 'RM ' + (Number(ride.unit_price || 0) * currentBooking.seats_requested).toFixed(2);
  document.getElementById('bkPriceNote').textContent = currentBooking.seats_requested + ' seat' + (currentBooking.seats_requested > 1 ? 's' : '') + ' total';
  document.getElementById('bkFrom').textContent = ride.from;
  document.getElementById('bkTo').textContent = ride.to;
  document.getElementById('bkDate').textContent = ride.date;
  document.getElementById('bkTime').textContent = ride.time;
  document.getElementById('bkPointsReward').textContent = '+' + (ride.points || 0) + ' green points';
  document.getElementById('searchForm').style.display = 'none';
  document.getElementById('availableRides').style.display = 'none';
  document.getElementById('bookingPanel').style.display = 'block';
  selectPayment('tng');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelBooking() {
  document.getElementById('bookingPanel').style.display = 'none';
  document.getElementById('searchForm').style.display = 'block';
  document.getElementById('availableRides').style.display = 'block';
}

function selectPayment(method) {
  selectedPayment = method;
  ['tng','cash','grab','bank'].forEach(function(m) {
    document.getElementById('pay-' + m).classList.toggle('selected', m === method);
  });
}

async function confirmBooking() {
  var labels = { tng: "Touch 'n Go", cash: 'Cash', grab: 'GrabPay', bank: 'Online Banking' };
  var formData = new FormData();
  formData.append('trip_id', currentBooking.trip_id);
  formData.append('payment_method', labels[selectedPayment]);
  formData.append('seats_requested', String(currentBooking.seats_requested || 1));

  try {
    var result = await apiPost('api/book-ride.php', formData);
    document.getElementById('bookingPanel').style.display = 'none';
    showReceiptPanel(currentBooking, labels[selectedPayment], result.request_id, result.amount_paid, result.status);
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

function showReceiptPanel(ride, payMethod, requestId, amountPaid, status) {
  var now = new Date();
  var timeStr = now.toLocaleTimeString('en-MY', { hour:'2-digit', minute:'2-digit' });
  var dateStr = now.toLocaleDateString('en-MY', { day:'numeric', month:'long', year:'numeric' });
  var bookingStatus = status === 'pending' ? 'Pending Driver Approval' : 'Booked';
  var pointsMessage = status === 'pending' ? 'Points will be added after driver approval' : 'Booking saved to database';
  var panel = document.getElementById('receiptPanel');
  panel.innerHTML = '<div class="section-title">Payment Receipt</div>'
    + '<div class="receipt-card">'
    + '<div class="receipt-stamp">✅</div>'
    + '<div class="receipt-title">Booking Submitted</div>'
    + '<div class="receipt-sub">Your ride request is now waiting for driver approval</div>'
    + '<div class="receipt-row"><span class="lbl">Driver</span><span class="val">' + escapeHtml(ride.name) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Vehicle</span><span class="val">' + escapeHtml(ride.car) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Plate No.</span><span class="val">' + escapeHtml(ride.plate) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">From</span><span class="val">' + escapeHtml(ride.from) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">To</span><span class="val">' + escapeHtml(ride.to) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Date</span><span class="val">' + escapeHtml(ride.date) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Departure</span><span class="val">' + escapeHtml(ride.time) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Seats</span><span class="val">' + (ride.seats_requested || 1) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Amount Paid</span><span class="val" style="color:var(--lime);">' + escapeHtml(amountPaid || ride.price) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Payment Method</span><span class="val">' + escapeHtml(payMethod) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Status</span><span class="val">' + escapeHtml(bookingStatus) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Confirmed At</span><span class="val">' + dateStr + ' ' + timeStr + '</span></div>'
    + '<div class="receipt-row" style="border-bottom:none;"><span class="lbl">Green Points</span><span class="val" style="color:var(--lime);">' + escapeHtml(pointsMessage) + '</span></div>'
    + '<div class="receipt-ref">Ride Request ID: <span>' + escapeHtml(String(requestId || '')) + '</span></div>'
    + '</div>'
    + '<div style="display:flex;gap:12px;">'
    + '<a href="my-trips.php" class="btn-outline" style="flex:1;text-align:center;display:flex;align-items:center;justify-content:center;">View My Trips</a>'
    + '<button class="btn-primary" style="flex:1;justify-content:center;" onclick="bookAnother()">Book Another</button>'
    + '</div>';
  panel.style.display = 'block';
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function bookAnother() {
  document.getElementById('receiptPanel').style.display = 'none';
  document.getElementById('searchForm').style.display = 'block';
  document.getElementById('availableRides').style.display = 'none';
  document.getElementById('pickupInput').value = '';
  document.getElementById('dropInput').value = '';
}

document.addEventListener('DOMContentLoaded', initRiderFindRides);
