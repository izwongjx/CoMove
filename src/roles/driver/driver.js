/* ============================================
   ECORIDE - Driver Dashboard Logic
   (Copied from original - no internal path changes needed)
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
  initDriverTabs();
  renderOngoingTrips();
  renderRequests();
  renderOfferedRides();
  renderTransactions();
  initCreateTrip();
});

function initDriverTabs() {
  var allBtns = document.querySelectorAll('[data-tab-btn]');
  var allContents = document.querySelectorAll('[data-tab-content]');
  allBtns.forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      var tabId = this.dataset.tabBtn;
      allBtns.forEach(function (b) {b.classList.remove('active');});
      document.querySelectorAll('[data-tab-btn="' + tabId + '"]').forEach(function (b) {b.classList.add('active');});
      allContents.forEach(function (c) {c.classList.remove('active');});
      var target = document.querySelector('[data-tab-content="' + tabId + '"]');
      if (target) target.classList.add('active');
    });
  });
}

var ongoingTrips = [
{ id: 'trip1', from: 'University Campus', to: 'Tech Park', startTime: '2:00 PM', riders: [{ id: 'r1', name: 'Alice Wong', amount: 5, method: 'cash', paid: false }, { id: 'r2', name: 'Charlie Brown', amount: 5, method: 'card', paid: false }] },
{ id: 'trip2', from: 'North Station', to: 'Downtown Mall', startTime: '11:00 AM', riders: [{ id: 'r3', name: 'Diana Lee', amount: 10, method: 'cash', paid: false }] }];


var requests = [
{ id: 'req1', name: 'Alice Wong', rating: 4.8, from: 'University Campus', to: 'Downtown Mall', time: 'Tomorrow, 8:30 AM', seats: 1, price: 5, method: 'cash' },
{ id: 'req2', name: 'Bob Smith', rating: 4.5, from: 'University Campus', to: 'Downtown Mall', time: 'Tomorrow, 8:30 AM', seats: 2, price: 10, method: 'card' }];


function renderOngoingTrips() {
  var container = document.getElementById('ongoingTripsContainer');if (!container) return;
  container.innerHTML = ongoingTrips.map(function (trip) {
    var allPaid = trip.riders.every(function (r) {return r.paid;});
    return '<div class="ongoing-trip-card" id="trip-' + trip.id + '"><div class="trip-status-bar"><div class="flex items-center gap-2"><div class="pulse-dot"></div> In Progress</div><span>Started ' + trip.startTime + '</span></div><div class="trip-body"><div class="flex items-center gap-3 mb-4"><strong class="text-lg">' + trip.from + '</strong> <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--gray-300);"/></svg> <strong class="text-lg">' + trip.to + '</strong></div><div class="text-xs font-bold uppercase text-gray-500 mb-3">Passenger Payments</div>' +
    trip.riders.map(function (rider) {
      return '<div class="rider-payment-row" id="rider-' + rider.id + '"><div class="rider-info"><div class="avatar avatar-md" style="background:var(--white);border:1px solid var(--gray-200);color:var(--gray-500);font-weight:700;">' + rider.name.charAt(0) + '</div><div><div class="font-bold text-sm">' + rider.name + '</div><div class="text-xs text-gray-500">$' + rider.amount.toFixed(2) + ' &bull; ' + rider.method.toUpperCase() + '</div></div></div><div>' + (rider.paid ? '<div class="paid-badge"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polyline points="20,6 9,17 4,12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Paid</div>' : '<label class="upload-proof-btn"><input type="file" style="display:none;" onchange="markPaid(\'' + trip.id + '\',\'' + rider.id + '\')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/><polyline points="17,8 12,3 7,8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><line x1="12" y1="3" x2="12" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Upload Proof</label>') + '</div></div>';
    }).join('') +
    '<button class="btn btn-full btn-sm ' + (allPaid ? 'btn-primary' : '') + '" ' + (!allPaid ? 'disabled style="background:var(--gray-100);color:var(--gray-400);cursor:not-allowed;"' : '') + ' onclick="completeTrip(\'' + trip.id + '\')">COMPLETE TRIP</button>' + (
    !allPaid ? '<p class="text-center text-xs text-gray-400 mt-2">Upload payment proof for all passengers to complete the trip.</p>' : '') +
    '</div></div>';
  }).join('');
}

function markPaid(tripId, riderId) {ongoingTrips.forEach(function (t) {if (t.id === tripId) t.riders.forEach(function (r) {if (r.id === riderId) r.paid = true;});});renderOngoingTrips();if (typeof initIcons === 'function') initIcons();}
function completeTrip(tripId) {ongoingTrips = ongoingTrips.filter(function (t) {return t.id !== tripId;});renderOngoingTrips();if (typeof initIcons === 'function') initIcons();if (ongoingTrips.length === 0) document.getElementById('ongoingTripsSection').style.display = 'none';}

function renderRequests() {
  var grid = document.getElementById('requestsGrid');if (!grid) return;
  grid.innerHTML = requests.map(function (req) {
    return '<div class="request-card" id="request-' + req.id + '"><div class="request-header"><div class="request-rider"><div class="avatar avatar-md" style="background:var(--gray-100);color:var(--gray-500);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/></svg></div><div><div class="font-bold text-sm">' + req.name + '</div><div class="text-xs text-gray-500"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" style="display:inline;vertical-align:middle;"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26" fill="var(--yellow-400)" stroke="var(--yellow-400)" stroke-width="1"/></svg> ' + req.rating.toFixed(1) + ' &bull; ' + req.seats + ' seat' + (req.seats > 1 ? 's' : '') + '</div></div></div><div class="text-right"><div class="font-black text-lg text-forest">$' + req.price.toFixed(2) + '</div><div class="text-xs text-gray-400">Potential Earning</div></div></div><div class="request-details"><div><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> ' + req.time + '</div><div><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" fill="none"/></svg> ' + req.from + ' → ' + req.to + '</div><div class="payment-badge ' + (req.method === 'card' ? 'payment-card' : 'payment-cash') + '">' + (req.method === 'card' ? '💳 Card Payment' : '💵 Cash Payment') + '</div></div><div class="request-actions"><button class="btn btn-danger btn-sm flex-1" onclick="rejectRequest(\'' + req.id + '\')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg> Decline</button><button class="btn btn-primary btn-sm flex-1" onclick="acceptRequest(\'' + req.id + '\')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none"><polyline points="20,6 9,17 4,12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> Accept</button></div></div>';
  }).join('');
  document.getElementById('requestCount').textContent = requests.length;
  if (requests.length === 0) document.getElementById('requestsSection').style.display = 'none';
}

function acceptRequest(id) {requests = requests.filter(function (r) {return r.id !== id;});renderRequests();}
function rejectRequest(id) {requests = requests.filter(function (r) {return r.id !== id;});renderRequests();}

function renderOfferedRides() {
  var container = document.getElementById('offeredRidesContainer');if (!container) return;
  container.innerHTML = '<div class="offered-ride-card"><div class="flex justify-between items-start flex-wrap gap-4"><div class="flex-1"><div class="flex items-center gap-2 mb-2"><span class="badge badge-lime">Tomorrow, 8:30 AM</span></div><div class="font-bold text-lg">University Campus → Downtown Mall</div></div><div class="text-right"><span class="text-2xl font-bold">$20.00</span><div class="text-xs text-gray-500">total trip</div></div></div><div class="flex items-center gap-4 text-sm text-gray-600 mt-3 mb-4"><span class="flex items-center gap-1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/><circle cx="7" cy="17" r="2" stroke="currentColor" stroke-width="2" fill="none"/><circle cx="17" cy="17" r="2" stroke="currentColor" stroke-width="2" fill="none"/></svg> Toyota Prius (ABC-123)</span><span class="flex items-center gap-1"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none"/></svg> 2/4 seats booked</span></div><button class="btn btn-primary btn-full btn-sm">Manage Ride</button></div>';
}

function renderTransactions() {
  var list = document.getElementById('transactionsList');if (!list) return;
  var txns = [{ date: 'Today, 2:30 PM', route: 'Campus → Downtown', amount: 15.50 }, { date: 'Yesterday, 8:45 AM', route: 'North Station → Tech Park', amount: 12.00 }, { date: 'Dec 12, 5:15 PM', route: 'Mall → Campus', amount: 8.50 }, { date: 'Dec 10, 9:00 AM', route: 'Airport → City Center', amount: 25.00 }];
  list.innerHTML = txns.map(function (tx) {
    return '<div class="transaction-item"><div class="flex items-center gap-3"><div class="section-icon" style="background:rgba(196,245,71,0.1);color:var(--forest);width:32px;height:32px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></div><div><div class="font-bold text-sm">' + tx.route + '</div><div class="text-xs text-gray-500">' + tx.date + '</div></div></div><div class="text-right"><div class="font-bold text-sm text-forest">+$' + tx.amount.toFixed(2) + '</div><span class="badge badge-green" style="font-size:10px;">Completed</span></div></div>';
  }).join('');
}

function initCreateTrip() {
  var form = document.getElementById('createTripForm');if (!form) return;
  form.addEventListener('submit', function (e) {e.preventDefault();var data = new FormData(this);console.log('Trip Created:', Object.fromEntries(data));closeModal('createTripModal');alert('Ride created successfully!');});
}