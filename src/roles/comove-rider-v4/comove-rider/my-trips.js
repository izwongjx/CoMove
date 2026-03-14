/* Comove – My Trips JS */

function initMyTrips() {
  // Set default date for request form to today
  var d = document.getElementById('reqDate');
  if (d) d.value = new Date().toISOString().split('T')[0];

  // Check URL hash to auto-open requests tab
  if (window.location.hash === '#requests') {
    var btn = document.getElementById('requestsTabBtn');
    if (btn) switchTripsTab(btn, 'requests');
  }
}

function switchTripsTab(el, tab) {
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  ['trip-history','requests'].forEach(function(t) {
    var s = document.getElementById('tab-' + t);
    if (s) s.style.display = (t === tab) ? 'block' : 'none';
  });
}

function postRequest() {
  var pickup = document.getElementById('reqPickup').value.trim();
  var drop = document.getElementById('reqDrop').value.trim();
  var date = document.getElementById('reqDate').value;
  var time = document.getElementById('reqTime').value;
  if (!pickup || !drop) { showToast('⚠️ Please enter pickup and destination'); return; }
  if (!date || !time) { showToast('⚠️ Please select date and time'); return; }
  showToast('📢 Ride request posted! Drivers will be notified.');
  // Clear form
  document.getElementById('reqPickup').value = '';
  document.getElementById('reqDrop').value = '';
  document.getElementById('reqTime').value = '';
}

function cancelRequest() {
  var r = document.getElementById('myRequest');
  if (r) {
    r.style.transition = 'opacity 0.3s, transform 0.3s';
    r.style.opacity = '0';
    r.style.transform = 'translateX(20px)';
    setTimeout(function(){ r.remove(); showToast('✅ Ride request cancelled.'); }, 300);
  }
}

function openTripDetail(driver, car, plate, from, to, date, duration, fare, payment, pts) {
  var initials = driver.split(' ').map(function(w){ return w[0]; }).join('').slice(0,2).toUpperCase();
  document.getElementById('md-from').textContent = from;
  document.getElementById('md-to').textContent = to;
  document.getElementById('md-duration').textContent = duration;
  document.getElementById('md-pts').textContent = pts + ' pts';
  document.getElementById('md-fare').textContent = fare;
  document.getElementById('md-payment').textContent = payment;
  document.getElementById('md-driver').textContent = driver;
  document.getElementById('md-car').textContent = car;
  document.getElementById('md-plate').textContent = plate;
  document.getElementById('md-ava').textContent = initials;
  document.getElementById('md-date').textContent = '📅 ' + date;
  document.getElementById('tripModal').classList.add('open');
}

function closeTripModal() {
  document.getElementById('tripModal').classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function() {
  var m = document.getElementById('tripModal');
  if (m) m.addEventListener('click', function(e){ if(e.target===m) closeTripModal(); });
  initMyTrips();
});
