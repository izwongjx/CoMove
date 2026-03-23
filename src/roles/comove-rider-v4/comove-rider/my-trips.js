/* Comove – My Trips JS */
function initMyTrips() {
  loadTrips();
}

function switchTripsTab(el, tab) {
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  ['trip-history'].forEach(function(t) {
    var s = document.getElementById('tab-' + t);
    if (s) s.style.display = (t === tab) ? 'block' : 'none';
  });
}

async function loadTrips() {
  try {
    var data = await apiGet('api/trips.php');
    renderTripsSummary(data.summary || {});
    renderBookedTripCard(data.upcoming);
    renderTripHistory(data.history || []);
  } catch (err) {
    showToast('⚠️ Unable to load trips');
  }
}

function renderTripsSummary(summary) {
  var totalTrips = document.getElementById('myTripsTotal');
  var totalPoints = document.getElementById('myTripsPoints');
  if (totalTrips) totalTrips.textContent = summary.total_trips || 0;
  if (totalPoints) totalPoints.textContent = summary.total_points || 0;
}

function renderBookedTripCard(trip) {
  var section = document.getElementById('upcomingRideSection');
  var card = document.getElementById('upcomingRideCard');
  if (!section || !card) return;

  if (!trip) {
    section.style.display = 'none';
    return;
  }

  card.innerHTML = '<div class="trip-detail-card" style="border-color:rgba(200,241,53,0.18);background:linear-gradient(135deg,rgba(20,30,8,0.96),rgba(13,18,10,0.96));">'
    + '<div class="trip-header">'
    + '<span class="trip-type-tag tag-carpool">Upcoming Ride</span>'
    + '<span style="font-size:12px;color:var(--lime);font-weight:600;">' + escapeHtml(trip.status) + '</span>'
    + '</div>'
    + '<div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">'
    + '<div class="driver-ava" style="width:42px;height:42px;"><img src="' + escapeHtml(trip.driver_photo_url) + '" alt="' + escapeHtml(trip.driver_name) + ' profile photo"></div>'
    + '<div style="font-size:12px;color:var(--gray-400);">Driver: <strong style="color:var(--white);">' + escapeHtml(trip.driver_name) + '</strong> · <span style="color:var(--lime);">' + escapeHtml(trip.plate_number) + '</span> · ' + escapeHtml(trip.vehicle_model) + '</div>'
    + '</div>'
    + '<div class="trip-route-display">'
    + '<div class="route-dot from"></div><div class="route-loc">' + escapeHtml(trip.from) + '</div>'
    + '<div class="route-line"></div>'
    + '<div class="route-loc">' + escapeHtml(trip.to) + '</div><div class="route-dot to"></div>'
    + '</div>'
    + '<div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--gray-400);margin-bottom:12px;">'
    + '<span>Pickup: <strong style="color:var(--white);font-weight:600;">' + escapeHtml(trip.date + ' · ' + trip.time) + '</strong></span>'
    + '<span>ETA: <strong style="color:var(--lime);font-weight:600;">' + escapeHtml(trip.eta) + '</strong></span>'
    + '</div>'
    + '<div class="trip-footer">'
    + '<div class="trip-stat"><div class="trip-stat-val">' + escapeHtml(trip.fare) + '</div><div class="trip-stat-lbl">Fare</div></div>'
    + '<div class="trip-stat"><div class="trip-stat-val" style="font-size:15px;">' + escapeHtml(trip.payment_method) + '</div><div class="trip-stat-lbl">Payment</div></div>'
    + '<div class="trip-stat"><div class="trip-stat-val" style="color:var(--lime);">+' + trip.points + ' pts</div><div class="trip-stat-lbl">Rewards</div></div>'
    + '</div>'
    + '<div style="margin-top:14px;display:flex;justify-content:flex-end;">'
    + '<button class="btn-danger-sm" onclick="cancelUpcomingTrip(' + trip.request_id + ')">Cancel Booking</button>'
    + '</div>'
    + '</div>';

  section.style.display = 'block';
}

function renderTripHistory(history) {
  var list = document.getElementById('tripHistoryList');
  if (!list) return;

  if (!history.length) {
    list.innerHTML = '<div class="form-card">No trips found yet.</div>';
    return;
  }

  list.innerHTML = history.map(function(trip) {
    return '<div class="trip-detail-card"'
      + ' data-driver="' + escapeHtml(trip.driver_name) + '"'
      + ' data-photo="' + escapeHtml(trip.driver_photo_url || '') + '"'
      + ' data-car="' + escapeHtml(trip.vehicle_model) + '"'
      + ' data-plate="' + escapeHtml(trip.plate_number) + '"'
      + ' data-from="' + escapeHtml(trip.from) + '"'
      + ' data-to="' + escapeHtml(trip.to) + '"'
      + ' data-date="' + escapeHtml(trip.label) + '"'
      + ' data-duration="' + escapeHtml(trip.duration) + '"'
      + ' data-fare="' + escapeHtml(trip.fare) + '"'
      + ' data-payment="' + escapeHtml(trip.payment_method) + '"'
      + ' data-points="+' + trip.points + '"'
      + ' onclick="openTripDetailFromCard(this)">'
      + '<div class="trip-header"><span class="trip-type-tag tag-carpool">🚗 Carpool</span><span style="font-size:12px;color:var(--gray-400);">' + escapeHtml(trip.label) + '</span></div>'
      + '<div style="font-size:12px;color:var(--gray-400);margin-bottom:10px;">Driver: <strong style="color:var(--white);">' + escapeHtml(trip.driver_name) + '</strong> · <span style="color:var(--lime);">' + escapeHtml(trip.plate_number) + '</span></div>'
      + '<div class="trip-route-display"><div class="route-dot from"></div><div class="route-loc">' + escapeHtml(trip.from) + '</div><div class="route-line"></div><div class="route-loc">' + escapeHtml(trip.to) + '</div><div class="route-dot to"></div></div>'
      + '<div class="trip-footer"><div class="trip-stat"><div class="trip-stat-val">' + escapeHtml(trip.duration) + '</div><div class="trip-stat-lbl">Duration</div></div><div class="trip-stat"><div class="trip-stat-val" style="color:var(--lime);">+' + trip.points + ' pts</div><div class="trip-stat-lbl">Earned</div></div><div class="trip-stat"><div class="trip-stat-val">' + escapeHtml(trip.fare) + '</div><div class="trip-stat-lbl">Paid</div></div></div>'
      + '</div>';
  }).join('');
}

function openTripDetailFromCard(el) {
  if (!el || !el.dataset) return;
  openTripDetail(
    el.dataset.driver || '',
    el.dataset.photo || '',
    el.dataset.car || '',
    el.dataset.plate || '',
    el.dataset.from || '',
    el.dataset.to || '',
    el.dataset.date || '',
    el.dataset.duration || '',
    el.dataset.fare || '',
    el.dataset.payment || '',
    el.dataset.points || '+0'
  );
}

function openTripDetail(driver, photoUrl, car, plate, from, to, date, duration, fare, payment, pts) {
  document.getElementById('md-from').textContent = from;
  document.getElementById('md-to').textContent = to;
  document.getElementById('md-duration').textContent = duration;
  document.getElementById('md-pts').textContent = pts + ' pts';
  document.getElementById('md-fare').textContent = fare;
  document.getElementById('md-payment').textContent = payment;
  document.getElementById('md-driver').textContent = driver;
  document.getElementById('md-car').textContent = car;
  document.getElementById('md-plate').textContent = plate;
  document.getElementById('md-ava').innerHTML = '<img src="' + escapeHtml(photoUrl) + '" alt="' + escapeHtml(driver) + ' profile photo" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">';
  document.getElementById('md-date').textContent = '📅 ' + date;
  document.getElementById('tripModal').classList.add('open');
}

async function cancelUpcomingTrip(requestId) {
  if (!requestId) return;

  var formData = new FormData();
  formData.append('action', 'cancel');
  formData.append('request_id', String(requestId));

  try {
    await apiPost('api/trips.php', formData);
    showToast('✅ Trip booking cancelled');
    loadTrips();
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

function closeTripModal() {
  document.getElementById('tripModal').classList.remove('open');
}

document.addEventListener('DOMContentLoaded', function() {
  var m = document.getElementById('tripModal');
  if (m) m.addEventListener('click', function(e){ if(e.target===m) closeTripModal(); });
  initMyTrips();
});
