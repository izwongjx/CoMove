/* ============================================
   ECORIDE - Rider Dashboard Logic
   (Same as original - no path changes needed in JS)
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
  initRiderTabs();
  renderAvailableRides();
  renderUpcomingRides();
  renderMyTrips();
  renderFriends();
  renderFriendRequests();
  renderRewards();
  initOngoingToggle();
  initFriendsTabs();
  initFindRides();
});

function initRiderTabs() {
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

var availableRides = [
{ id: 1, from: 'University Campus', to: 'Downtown Mall', depTime: '08:30 AM', arrTime: '09:15 AM', duration: '45 min', price: 22, driver: 'Sarah Chen', rating: 4.9, seats: 3, date: 'Tomorrow' },
{ id: 2, from: 'North Station', to: 'Tech Park', depTime: '07:00 AM', arrTime: '07:35 AM', duration: '35 min', price: 15, driver: 'Mike Johnson', rating: 4.8, seats: 2, date: 'Tomorrow' },
{ id: 3, from: 'Airport Terminal', to: 'City Center', depTime: '02:30 PM', arrTime: '03:45 PM', duration: '1h 15m', price: 45, driver: 'Emma Davis', rating: 5.0, seats: 4, date: 'Today' },
{ id: 4, from: 'Westside Mall', to: 'University Campus', depTime: '05:00 PM', arrTime: '05:40 PM', duration: '40 min', price: 18, driver: 'Alex Rivera', rating: 4.7, seats: 1, date: 'Tomorrow' }];


var upcomingRides = [
{ id: 10, from: 'University Campus', to: 'Downtown Mall', depTime: '08:30 AM', arrTime: '09:15 AM', duration: '45 min', price: 22, driver: 'Sarah Chen', rating: 4.9, seats: 3, date: 'Tomorrow, Dec 15' },
{ id: 11, from: 'Home', to: 'Airport Terminal', depTime: '06:00 AM', arrTime: '07:30 AM', duration: '1h 30m', price: 45, driver: 'Mike Johnson', rating: 4.8, seats: 2, date: 'Dec 20' }];


var completedTrips = [
{ id: 101, from: 'North Station', to: 'Tech Park', date: 'Dec 12, 2024', time: '5:00 PM', price: 16.50, driver: 'Mike Johnson', rating: 0 },
{ id: 102, from: 'University Campus', to: 'Downtown Mall', date: 'Dec 08, 2024', time: '8:30 AM', price: 12.00, driver: 'Sarah Chen', rating: 5 }];


var allFindRides = [
{ id: 1, from: 'University Campus', to: 'Downtown Mall', depTime: '08:30 AM', arrTime: '09:15 AM', duration: '45 min', price: 22, driver: 'Sarah Chen', rating: 4.9, seats: 3, date: 'Tomorrow', category: 'Campus' },
{ id: 2, from: 'North Station', to: 'Tech Park', depTime: '07:00 AM', arrTime: '07:35 AM', duration: '35 min', price: 15, driver: 'Mike Johnson', rating: 4.8, seats: 2, date: 'Tomorrow', category: 'Commute' },
{ id: 3, from: 'Airport Terminal', to: 'City Center', depTime: '02:30 PM', arrTime: '03:45 PM', duration: '1h 15m', price: 45, driver: 'Emma Davis', rating: 5.0, seats: 4, date: 'Today', category: 'Airport' },
{ id: 4, from: 'Westside Mall', to: 'University Campus', depTime: '05:00 PM', arrTime: '05:40 PM', duration: '40 min', price: 18, driver: 'Alex Rivera', rating: 4.7, seats: 1, date: 'Tomorrow', category: 'Shopping' },
{ id: 5, from: 'Downtown', to: 'Suburbs', depTime: '06:15 PM', arrTime: '07:00 PM', duration: '45 min', price: 25, driver: 'David Kim', rating: 4.6, seats: 3, date: 'Today', category: 'Commute' }];


function rideCardHTML(ride, bookable) {
  var initials = ride.driver.split(' ').map(function (n) {return n[0];}).join('');
  return '<div class="ride-card">' + (
  ride.sharedBy ? '<div class="shared-badge"><svg width="12" height="12" viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/><circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/><circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49" stroke="currentColor" stroke-width="2"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49" stroke="currentColor" stroke-width="2"/></svg> Shared by ' + ride.sharedBy + '</div>' : '') +
  '<div class="ride-card-header"><div class="ride-date-badge">' + ride.date + '</div><div class="ride-price"><div class="price-value">$' + ride.price.toFixed(2) + '</div><div class="price-label">total trip</div></div></div>' +
  '<div class="ride-route"><div class="route-stop"><div class="route-dot-col"><div class="dot dot-start"></div><div class="route-line-segment"></div></div><div class="route-stop-info"><div class="time">' + ride.depTime + '</div><div class="place">' + ride.from + '</div></div></div><div class="route-stop"><div class="route-dot-col"><div class="dot dot-end"></div></div><div class="route-stop-info"><div class="time">' + ride.arrTime + ' ETA</div><div class="place">' + ride.to + '</div></div></div></div>' +
  '<div class="ride-card-footer"><div class="driver-info"><div class="driver-avatar">' + initials + '</div><div><div class="driver-name">' + ride.driver + '</div><div class="driver-meta">★ ' + ride.rating.toFixed(1) + ' • ' + ride.seats + ' seats</div></div></div><button class="btn btn-primary btn-sm" onclick="' + (bookable ? 'openRideModal(' + ride.id + ')' : 'openViewRideModal(' + ride.id + ')') + '">' + (bookable ? 'Request' : 'View') + '</button></div></div>';
}

function renderAvailableRides() {var grid = document.getElementById('availableRidesGrid');if (!grid) return;grid.innerHTML = availableRides.map(function (r) {return rideCardHTML(r, true);}).join('');}
function renderUpcomingRides() {var grid = document.getElementById('upcomingRidesGrid');if (!grid) return;grid.innerHTML = upcomingRides.map(function (r) {return rideCardHTML(r, false);}).join('');}

function renderMyTrips() {
  var grid = document.getElementById('myTripsGrid');
  if (grid) grid.innerHTML = upcomingRides.map(function (r) {return rideCardHTML(r, false);}).join('');
  var pastGrid = document.getElementById('pastTripsGrid');
  if (pastGrid) {
    pastGrid.innerHTML = completedTrips.map(function (t) {
      return '<div class="past-trip"><div class="flex items-center justify-between mb-3"><div class="font-bold text-sm text-gray-500">' + t.date + ' &bull; ' + t.time + '</div><span class="badge badge-gray">Completed</span></div><div class="font-bold text-lg mb-1">' + t.from + ' &rarr; ' + t.to + '</div><div class="flex justify-between items-center"><div class="text-sm text-gray-500">Paid $' + t.price.toFixed(2) + '</div>' + (t.rating === 0 ? '<button class="btn btn-sm btn-outline" onclick="openReviewModal(' + t.id + ')">Write Review</button>' : '<div class="text-yellow-400 text-sm">★ ' + t.rating + '</div>') + '</div></div>';
    }).join('');
  }
}

function initOngoingToggle() {
  var btn = document.getElementById('toggleOngoing');var details = document.getElementById('ongoingDetails');
  if (!btn || !details) return;
  btn.addEventListener('click', function () {var showing = details.style.display !== 'none';details.style.display = showing ? 'none' : 'block';btn.textContent = showing ? 'View Trip Details' : 'Close Details';});
}

function initFriendsTabs() {
  document.querySelectorAll('.friends-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {
      document.querySelectorAll('.friends-tab').forEach(function (t) {t.classList.remove('active');});
      this.classList.add('active');
      document.querySelectorAll('.friends-panel').forEach(function (p) {p.classList.remove('active');});
      var target = document.getElementById(this.dataset.ftab);
      if (target) target.classList.add('active');
    });
  });
}

function renderFriends() {
  var grid = document.getElementById('friendsGrid');if (!grid) return;
  var friends = [{ name: 'Sarah Chen', status: 'online', mutual: 3 }, { name: 'Mike Johnson', status: 'offline', mutual: 1 }, { name: 'Emma Davis', status: 'online', mutual: 8 }];
  grid.innerHTML = friends.map(function (f) {
    return '<div class="friend-card"><div class="friend-avatar-wrap"><div class="avatar avatar-lg" style="background:var(--gray-100);color:var(--gray-400);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/></svg></div><div class="friend-status ' + (f.status === 'online' ? 'status-online' : 'status-offline') + '"></div></div><div class="flex-1"><div class="font-bold">' + f.name + '</div><div class="text-xs text-gray-500">' + f.mutual + ' mutual friends</div></div></div>';
  }).join('');
}

function renderFriendRequests() {
  var list = document.getElementById('requestsList');if (!list) return;
  var requests = [{ id: '1', name: 'David Wilson' }, { id: '2', name: 'Lisa Park' }];
  list.innerHTML = requests.map(function (r) {
    return '<div class="friend-request-card" id="req-' + r.id + '"><div class="avatar avatar-lg" style="background:var(--gray-100);color:var(--gray-400);"><svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" fill="none"/></svg></div><div class="flex-1"><div class="font-bold">' + r.name + '</div><div class="text-xs text-gray-500">Wants to be friends</div></div><div class="friend-request-actions"><button class="fr-accept" onclick="acceptRequest(\'' + r.id + '\')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><polyline points="20,6 9,17 4,12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button><button class="fr-reject" onclick="rejectRequest(\'' + r.id + '\')"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button></div></div>';
  }).join('');
}

function acceptRequest(id) {var el = document.getElementById('req-' + id);if (el) el.remove();}
function rejectRequest(id) {var el = document.getElementById('req-' + id);if (el) el.remove();}

function renderRewards() {
  var grid = document.getElementById('rewardsGrid');if (!grid) return;
  var rewards = [{ id: '1', name: '$5 Coffee Voucher', points: 500, category: 'Food & Drink', stock: 50 }, { id: '2', name: 'University Bookstore Discount', points: 1000, category: 'Education', stock: 25 }, { id: '3', name: 'Free Bus Pass (1 Day)', points: 800, category: 'Transport', stock: 100 }, { id: '4', name: 'EcoRide T-Shirt', points: 2000, category: 'Merch', stock: 5 }];
  var userPoints = 1250;
  grid.innerHTML = rewards.map(function (r) {
    var canRedeem = userPoints >= r.points && r.stock > 0;
    return '<div class="reward-card"><div class="reward-card-img"><svg width="40" height="40" viewBox="0 0 24 24" fill="none"><polyline points="20,12 20,22 4,22 4,12" stroke="currentColor" stroke-width="2" fill="none"/><rect x="2" y="7" width="20" height="5" stroke="currentColor" stroke-width="2" fill="none"/><line x1="12" y1="22" x2="12" y2="7" stroke="currentColor" stroke-width="2"/></svg><div class="reward-category">' + r.category + '</div></div><div class="reward-card-body"><h3>' + r.name + '</h3><div class="reward-points"><span class="pts-value">' + r.points + '</span><span class="pts-label">Points</span></div><div class="reward-stock"><span>Stock</span><span class="' + (r.stock < 10 ? 'stock-low' : '') + '">' + r.stock + ' left</span></div><button class="btn btn-full btn-sm ' + (canRedeem ? 'btn-primary' : '') + '" ' + (!canRedeem ? 'disabled' : '') + '>' + (r.stock === 0 ? 'Out of Stock' : canRedeem ? 'Redeem' : 'Not Enough') + '</button></div></div>';
  }).join('');
}

function initFindRides() {
  var dateFilter = 'All';var priceSort = null;
  document.querySelectorAll('.date-tab').forEach(function (tab) {
    tab.addEventListener('click', function () {document.querySelectorAll('.date-tab').forEach(function (t) {t.classList.remove('active');});this.classList.add('active');dateFilter = this.dataset.date;renderFindRides(dateFilter, priceSort);});
  });
  var sortBtn = document.getElementById('priceSortBtn');
  if (sortBtn) {sortBtn.addEventListener('click', function () {priceSort = priceSort === 'asc' ? 'desc' : 'asc';this.textContent = 'Price ' + (priceSort === 'asc' ? '↑' : '↓');renderFindRides(dateFilter, priceSort);});}
  renderFindRides('All', null);
}

function renderFindRides(dateFilter, priceSort) {
  var grid = document.getElementById('findRidesGrid');var countEl = document.getElementById('ridesCount');if (!grid) return;
  var filtered = allFindRides.filter(function (r) {return dateFilter === 'All' || r.date === dateFilter;});
  if (priceSort === 'asc') filtered.sort(function (a, b) {return a.price - b.price;});
  if (priceSort === 'desc') filtered.sort(function (a, b) {return b.price - a.price;});
  if (countEl) countEl.textContent = filtered.length + ' Rides Available';
  grid.innerHTML = filtered.map(function (r) {return rideCardHTML(r, true);}).join('');
  if (filtered.length === 0) {grid.innerHTML = '<div style="text-align:center;padding:48px;background:var(--white);border:1px solid var(--gray-200);border-radius:12px;grid-column:1/-1;"><h3 style="font-weight:700;font-size:18px;margin-bottom:8px;">No rides found</h3><p style="color:var(--gray-500);font-size:14px;">Try adjusting your search criteria or date filter.</p></div>';}
}

function openRideModal(rideId) {
  var ride = availableRides.concat(allFindRides).find(function (r) {return r.id === rideId;});if (!ride) return;
  var body = document.getElementById('rideModalBody');
  body.innerHTML = '<div style="margin-bottom:16px;"><div class="ride-date-badge" style="margin-bottom:8px;">' + ride.date + '</div><div style="font-weight:900;font-size:18px;margin-bottom:4px;">' + ride.from + ' → ' + ride.to + '</div><div style="font-size:13px;color:var(--gray-500);">' + ride.depTime + ' - ' + ride.arrTime + ' (' + ride.duration + ')</div></div><div style="margin-bottom:16px;"><label class="form-label">Number of Seats</label><select class="form-input" id="modalSeats"><option value="1">1 Seat</option><option value="2">2 Seats</option><option value="3">3 Seats</option></select></div><div style="margin-bottom:16px;"><label class="form-label">Payment Method</label><div style="display:flex;gap:8px;"><button type="button" class="btn btn-outline flex-1 payment-opt active" data-pay="cash" onclick="selectPayment(this)">Cash</button><button type="button" class="btn btn-outline flex-1 payment-opt" data-pay="card" onclick="selectPayment(this)">Card</button></div></div><div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:var(--gray-50);border-radius:8px;margin-bottom:16px;"><span style="font-weight:700;">Total</span><span style="font-size:24px;font-weight:900;color:var(--forest);">$' + ride.price.toFixed(2) + '</span></div><button class="btn btn-primary btn-full btn-lg" onclick="submitRideRequest(' + ride.id + ')">Confirm Request</button>';
  openModal('rideRequestModal');
}

function selectPayment(btn) {document.querySelectorAll('.payment-opt').forEach(function (b) {b.classList.remove('active');b.style.background = '';b.style.color = '';});btn.classList.add('active');btn.style.background = 'var(--black)';btn.style.color = 'var(--white)';}
function submitRideRequest(rideId) {closeModal('rideRequestModal');alert('Ride request sent!');}

function openViewRideModal(rideId) {
  var ride = upcomingRides.find(function (r) {return r.id === rideId;});if (!ride) return;
  var body = document.getElementById('viewRideModalBody');if (!body) return;
  body.innerHTML = '<div class="mb-4"><div class="ride-date-badge mb-2">' + ride.date + '</div><h3 class="text-xl font-black">' + ride.from + ' &rarr; ' + ride.to + '</h3><div class="text-gray-500">' + ride.depTime + ' - ' + ride.arrTime + ' (' + ride.duration + ')</div></div><div class="bg-gray-50 p-4 rounded-xl mb-4"><div class="flex items-center justify-between mb-2"><span class="text-gray-500 text-sm font-bold uppercase">Driver</span><span class="font-bold">' + ride.driver + '</span></div><div class="flex items-center justify-between"><span class="text-gray-500 text-sm font-bold uppercase">Vehicle</span><span class="font-bold">Toyota Prius (ABC-123)</span></div></div><div class="flex gap-3"><button class="btn btn-danger flex-1" onclick="cancelUpcomingRide(' + ride.id + ')">Cancel Ride</button><button class="btn btn-outline flex-1" onclick="closeModal(\'viewRideModal\')">Close</button></div>';
  openModal('viewRideModal');
}

function cancelUpcomingRide(id) {if (!confirm('Cancel this ride?')) return;upcomingRides = upcomingRides.filter(function (r) {return r.id !== id;});renderUpcomingRides();renderMyTrips();closeModal('viewRideModal');}

var currentReviewTripId = null;var currentRating = 0;
function openReviewModal(tripId) {currentReviewTripId = tripId;currentRating = 0;updateStars();var textEl = document.getElementById('reviewText');if (textEl) textEl.value = '';openModal('reviewModal');}
function setRating(n) {currentRating = n;updateStars();}
function updateStars() {var container = document.getElementById('reviewStars');if (!container) return;var html = '';for (var i = 1; i <= 5; i++) {html += '<span class="cursor-pointer text-2xl ' + (i <= currentRating ? 'text-yellow-400' : 'text-gray-300') + '" onclick="setRating(' + i + ')">★</span>';}container.innerHTML = html;}
function submitReview() {var trip = completedTrips.find(function (t) {return t.id === currentReviewTripId;});if (trip) trip.rating = currentRating;renderMyTrips();closeModal('reviewModal');}