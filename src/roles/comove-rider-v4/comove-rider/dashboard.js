/* Comove – Rider Dashboard JS */
var dbSelectedPayment = 'tng';
var dbCurrentRide = {};
var tripTimerInterval = null;
var tripSeconds = 754; // 12:34 starting elapsed

function initRiderDashboard() {
  var el = document.getElementById('dashDate');
  if (el) {
    var now = new Date();
    var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    el.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear() + ' · Rider Account';
  }
  startTripTimer();
}

/* ── Live trip timer ── */
function startTripTimer() {
  var timerEl = document.getElementById('tripTimer');
  if (!timerEl) return;
  tripTimerInterval = setInterval(function() {
    tripSeconds++;
    var m = Math.floor(tripSeconds / 60);
    var s = tripSeconds % 60;
    timerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
  }, 1000);
}

/* ── Cancel ongoing trip ── */
function cancelOngoingTrip() {
  document.getElementById('cancelTripModal').classList.add('open');
}
function doCancel() {
  clearInterval(tripTimerInterval);
  document.getElementById('cancelTripModal').classList.remove('open');
  document.getElementById('ongoingTripSection').style.display = 'none';
  showToast('🚫 Trip cancelled. RM 1.00 cancellation fee applied.');
}

/* ── Dashboard quick booking ── */
function dashBook(name, car, plate, price, time, from, to, bg, initials) {
  dbCurrentRide = { name: name, car: car, plate: plate, price: price, time: time, from: from, to: to };
  document.getElementById('dbAva').textContent = initials;
  document.getElementById('dbAva').style.background = bg;
  document.getElementById('dbName').textContent = name;
  document.getElementById('dbCarPlate').textContent = car + ' · ' + plate;
  document.getElementById('dbPrice').textContent = price;
  document.getElementById('dbFrom').textContent = from;
  document.getElementById('dbTo').textContent = to;
  document.getElementById('dbTime').textContent = time;
  dbSelectPayment('tng');
  document.getElementById('dashBookModal').classList.add('open');
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
function dbConfirmBooking() {
  closeDashBookModal();
  var labels = { tng: "Touch 'n Go", cash: 'Cash', grab: 'GrabPay', bank: 'Online Banking' };
  var ref = 'CMV-' + Math.random().toString(36).substr(2,8).toUpperCase();
  showReceipt(dbCurrentRide, labels[dbSelectedPayment], ref);
}

function showReceipt(ride, payMethod, ref) {
  var now = new Date();
  var timeStr = now.toLocaleTimeString('en-MY', { hour:'2-digit', minute:'2-digit' });
  var dateStr = now.toLocaleDateString('en-MY', { day:'numeric', month:'long', year:'numeric' });
  document.getElementById('receiptContent').innerHTML =
    '<div class="receipt-card">'
    + '<div class="receipt-stamp">✅</div>'
    + '<div class="receipt-title">Payment Confirmed</div>'
    + '<div class="receipt-sub">Your ride has been booked successfully</div>'
    + '<div class="receipt-row"><span class="lbl">Driver</span><span class="val">' + ride.name + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Vehicle</span><span class="val">' + ride.car + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Plate No.</span><span class="val" style="color:var(--lime);font-family:monospace;">' + ride.plate + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">From</span><span class="val">' + ride.from + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">To</span><span class="val">' + ride.to + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Departure</span><span class="val">' + ride.time + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Amount Paid</span><span class="val" style="color:var(--lime);">' + ride.price + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Payment</span><span class="val">' + payMethod + '</span></div>'
    + '<div class="receipt-row"><span class="lbl">Date & Time</span><span class="val">' + dateStr + ' ' + timeStr + '</span></div>'
    + '<div class="receipt-row" style="border-bottom:none;"><span class="lbl">Green Points</span><span class="val" style="color:var(--lime);">+45 pts 🌿</span></div>'
    + '<div class="receipt-ref">Reference: <span>' + ref + '</span></div>'
    + '</div>'
    + '<button class="btn-primary" style="width:100%;justify-content:center;" onclick="closeReceipt()">Done</button>';
  document.getElementById('receiptModal').classList.add('open');
}
function closeReceipt() {
  document.getElementById('receiptModal').classList.remove('open');
}

/* ── Switch to Driver ── */
function switchToDriver() { document.getElementById('switchDriverModal').classList.add('open'); }
function closeSwitchModal() { document.getElementById('switchDriverModal').classList.remove('open'); }
function doSwitchDriver() {
  closeSwitchModal();
  showToast('🚗 Switching to Driver account...');
  setTimeout(function(){ showToast('✅ Welcome to your Driver dashboard!'); }, 1500);
}

document.addEventListener('DOMContentLoaded', function() {
  ['dashBookModal','switchDriverModal','receiptModal','cancelTripModal'].forEach(function(id) {
    var m = document.getElementById(id);
    if (m) m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
  });
});

initRiderDashboard();
