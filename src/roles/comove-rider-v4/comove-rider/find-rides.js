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

function searchRides() {
  var pickup = document.getElementById('pickupInput').value.trim();
  var drop = document.getElementById('dropInput').value.trim();
  if (!pickup || !drop) { showToast('⚠️ Please enter pickup and destination'); return; }
  showToast('🔍 Searching available rides...');
  setTimeout(function() {
    document.getElementById('availableRides').style.display = 'block';
    document.getElementById('availableRides').scrollIntoView({ behavior: 'smooth' });
    showToast('✅ 3 rides found near you!');
  }, 1000);
}

function goToBooking(name, car, plate, price, time, rating, trips, bg, initials) {
  var pickup = document.getElementById('pickupInput').value.trim() || 'APU Campus';
  var drop = document.getElementById('dropInput').value.trim() || 'Destination';
  var date = document.getElementById('dateInput').value || 'Today';
  currentBooking = { name: name, car: car, plate: plate, price: price, time: time, from: pickup, to: drop, date: date };
  document.getElementById('bkAva').textContent = initials;
  document.getElementById('bkAva').style.background = bg;
  document.getElementById('bkName').textContent = name;
  document.getElementById('bkCar').textContent = car + ' · ' + plate;
  document.getElementById('bkRating').textContent = rating + ' (' + trips + ' trips)';
  document.getElementById('bkPrice').textContent = price;
  document.getElementById('bkFrom').textContent = pickup;
  document.getElementById('bkTo').textContent = drop;
  document.getElementById('bkDate').textContent = date;
  document.getElementById('bkTime').textContent = time;
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

function confirmBooking() {
  var labels = { tng: "Touch 'n Go", cash: 'Cash', grab: 'GrabPay', bank: 'Online Banking' };
  var ref = 'CMV-' + Math.random().toString(36).substr(2,8).toUpperCase();
  document.getElementById('bookingPanel').style.display = 'none';
  showReceiptPanel(currentBooking, labels[selectedPayment], ref);
}

function showReceiptPanel(ride, payMethod, ref) {
  var now = new Date();
  var timeStr = now.toLocaleTimeString('en-MY', { hour:'2-digit', minute:'2-digit' });
  var dateStr = now.toLocaleDateString('en-MY', { day:'numeric', month:'long', year:'numeric' });
  var panel = document.getElementById('receiptPanel');
  panel.innerHTML = '<div class="section-title">Payment Receipt</div>'
    + '<div class="receipt-card">'
    + '<div class="receipt-stamp">✅</div>'
    + '<div class="receipt-title">Payment Confirmed</div>'
    + '<div class="receipt-sub">Your ride has been booked successfully</div>'
    + '<div class="receipt-row"><span class="lbl">Driver</span><span class="val">' + ride.name + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Vehicle</span><span class="val">' + ride.car + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Plate No.</span><span class="val">' + ride.plate + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">From</span><span class="val">' + ride.from + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">To</span><span class="val">' + ride.to + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Date</span><span class="val">' + ride.date + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Departure</span><span class="val">' + ride.time + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Amount Paid</span><span class="val" style="color:var(--lime);">' + ride.price + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Payment Method</span><span class="val">' + payMethod + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Confirmed At</span><span class="val">' + dateStr + ' ' + timeStr + '</span></div>'
    + '<div class="receipt-row" style="border-bottom:none;"><span class="lbl">Green Points</span><span class="val" style="color:var(--lime);">+45 pts earned 🌿</span></div>'
    + '<div class="receipt-ref">Reference No: <span>' + ref + '</span></div>'
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

initRiderFindRides();
