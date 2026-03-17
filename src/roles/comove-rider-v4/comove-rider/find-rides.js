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

async function searchRides() {
  var pickup = document.getElementById('pickupInput').value.trim();
  var drop = document.getElementById('dropInput').value.trim();
  if (!pickup || !drop) { showToast('⚠️ Please enter pickup and destination'); return; }

  try {
    var data = await apiGet('api/rides.php?pickup=' + encodeURIComponent(pickup) + '&drop=' + encodeURIComponent(drop));
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
      + '<div class="driver-ava">' + escapeHtml(ride.driver_initials) + '</div>'
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
    time: ride.time,
    from: ride.from,
    to: ride.to,
    date: ride.date
  };

  document.getElementById('bkAva').textContent = ride.driver_initials;
  document.getElementById('bkName').textContent = ride.driver_name;
  document.getElementById('bkCar').textContent = ride.vehicle_model + ' · ' + ride.plate_number;
  document.getElementById('bkRating').textContent = 'Driver available';
  document.getElementById('bkPrice').textContent = ride.price;
  document.getElementById('bkFrom').textContent = ride.from;
  document.getElementById('bkTo').textContent = ride.to;
  document.getElementById('bkDate').textContent = ride.date;
  document.getElementById('bkTime').textContent = ride.time;
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
  formData.append('seats_requested', '1');

  try {
    var result = await apiPost('api/book-ride.php', formData);
    document.getElementById('bookingPanel').style.display = 'none';
    showReceiptPanel(currentBooking, labels[selectedPayment], result.reference, result.amount_paid);
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

function showReceiptPanel(ride, payMethod, ref, amountPaid) {
  var now = new Date();
  var timeStr = now.toLocaleTimeString('en-MY', { hour:'2-digit', minute:'2-digit' });
  var dateStr = now.toLocaleDateString('en-MY', { day:'numeric', month:'long', year:'numeric' });
  var panel = document.getElementById('receiptPanel');
  panel.innerHTML = '<div class="section-title">Payment Receipt</div>'
    + '<div class="receipt-card">'
    + '<div class="receipt-stamp">✅</div>'
    + '<div class="receipt-title">Payment Confirmed</div>'
    + '<div class="receipt-sub">Your ride has been booked successfully</div>'
    + '<div class="receipt-row"><span class="lbl">Driver</span><span class="val">' + escapeHtml(ride.name) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Vehicle</span><span class="val">' + escapeHtml(ride.car) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Plate No.</span><span class="val">' + escapeHtml(ride.plate) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">From</span><span class="val">' + escapeHtml(ride.from) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">To</span><span class="val">' + escapeHtml(ride.to) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Date</span><span class="val">' + escapeHtml(ride.date) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Departure</span><span class="val">' + escapeHtml(ride.time) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Amount Paid</span><span class="val" style="color:var(--lime);">' + escapeHtml(amountPaid || ride.price) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Payment Method</span><span class="val">' + escapeHtml(payMethod) + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Confirmed At</span><span class="val">' + dateStr + ' ' + timeStr + '</span></div>'
    + '<div class="receipt-row" style="border-bottom:none;"><span class="lbl">Green Points</span><span class="val" style="color:var(--lime);">Booking saved to database</span></div>'
    + '<div class="receipt-ref">Reference No: <span>' + escapeHtml(ref) + '</span></div>'
    + '</div>'
    + '<div style="display:flex;gap:12px;">'
    + '<a href="my-trips.html" class="btn-outline" style="flex:1;text-align:center;display:flex;align-items:center;justify-content:center;">View My Trips</a>'
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
