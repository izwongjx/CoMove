/* ============================================
   ECORIDE - Admin Dashboard Logic
   (Copied to src/ - no internal path changes needed)
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
  initAdminTabs();
  renderAdminStats();
  renderApprovals();
  renderLiveActivity();
  renderUsers();
  renderAdminRewards();
  renderLogs();
  renderAnalytics();
  renderSettings();
  renderAdminProfile();
  initRewardForm();
});

function initAdminTabs() {
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

function statCardHTML(label, value, change, changeType, color) {
  var colorMap = { lime: 'rgba(196,245,71,0.1)', forest: 'rgba(15,42,29,0.1)', black: 'rgba(0,0,0,0.05)', yellow: 'rgba(250,204,21,0.1)', red: 'rgba(239,68,68,0.1)' };
  var changeBg = changeType === 'positive' ? 'background:var(--green-100);color:var(--green-700);' : changeType === 'negative' ? 'background:var(--red-100);color:var(--red-700);' : 'background:var(--gray-100);color:var(--gray-600);';
  return '<div class="stat-card"><div class="flex items-center justify-between mb-3"><div class="section-icon" style="background:' + (colorMap[color] || colorMap.black) + ';width:40px;height:40px;"></div>' + (change ? '<span class="badge" style="' + changeBg + '">' + change + '</span>' : '') + '</div><div class="text-2xl font-black">' + value + '</div><div class="text-sm text-gray-500">' + label + '</div></div>';
}

function renderAdminStats() {
  var grid = document.getElementById('adminStatsGrid');if (!grid) return;
  grid.innerHTML = statCardHTML('Total Riders', '2,547', '+12.5%', 'positive', 'forest') + statCardHTML('Active Drivers', '342', '+8.2%', 'positive', 'lime') + statCardHTML('Pending Approvals', '2', '', 'neutral', 'yellow') + statCardHTML('Total Trip Value', '$45,230', '+15.3%', 'positive', 'black');
}

var drivers = [
{ id: 'D00001', name: 'James Wilson', email: 'james.w@email.com', phone: '+1 (555) 234-5678', nric: 'S1234567A', vehicle: 'Toyota Camry 2021', plate: 'SGX-1234', applied: '2 hours ago', status: 'pending', docs: { nric: true, psv: true, insurance: true, roadtax: true, ownership: false } },
{ id: 'D00002', name: 'Linda Martinez', email: 'linda.m@email.com', phone: '+1 (555) 345-6789', nric: 'S2345678B', vehicle: 'Honda Civic 2022', plate: 'SGX-5678', applied: '5 hours ago', status: 'pending', docs: { nric: true, psv: true, insurance: true, roadtax: true, ownership: true } },
{ id: 'D00003', name: 'Robert Chen', email: 'robert.c@email.com', phone: '+1 (555) 987-6543', nric: 'S3456789C', vehicle: 'Hyundai Ioniq', plate: 'SGY-9012', applied: '1 day ago', status: 'rejected', docs: { nric: true, psv: false, insurance: true, roadtax: true, ownership: true } }];

var approvalFilter = 'pending';

function renderApprovals() {
  var grid = document.getElementById('approvalGrid');if (!grid) return;
  document.querySelectorAll('#approvalFilter .filter-btn').forEach(function (btn) {btn.classList.toggle('active', btn.dataset.filter === approvalFilter);btn.onclick = function () {approvalFilter = this.dataset.filter;renderApprovals();};});
  var filtered = drivers.filter(function (d) {return d.status === approvalFilter;});
  if (filtered.length === 0) {grid.innerHTML = '<div style="text-align:center;padding:48px;background:var(--white);border:1px solid var(--gray-200);border-radius:12px;grid-column:1/-1;"><h3 class="font-bold text-lg mb-2">No ' + approvalFilter + ' applications</h3><p class="text-gray-500 text-sm">There are no drivers in this category.</p></div>';return;}
  grid.innerHTML = filtered.map(function (d) {
    var allDocs = Object.values(d.docs).every(Boolean);
    return '<div class="approval-card"><div class="approval-header"><div class="flex items-center gap-3"><div class="avatar avatar-lg" style="background:var(--gray-200);color:var(--gray-600);">' + d.name.charAt(0) + '</div><div><div class="font-bold">' + d.name + '</div><div class="text-sm text-gray-500">' + d.email + '</div><div class="text-xs text-gray-400 mt-1">Applied ' + d.applied + '</div></div></div><span class="badge ' + (allDocs ? 'badge-green' : 'badge-yellow') + '">' + (allDocs ? '✓ Complete' : '⚠ Incomplete') + '</span></div><div class="approval-info-grid"><div><div class="info-label">NRIC</div><div class="info-value font-mono">' + d.nric + '</div></div><div><div class="info-label">Phone</div><div class="info-value font-mono">' + d.phone + '</div></div><div><div class="info-label">Vehicle</div><div class="info-value">' + d.vehicle + '</div></div><div><div class="info-label">Plate</div><div class="info-value font-mono">' + d.plate + '</div></div></div><div class="approval-actions"><button class="btn btn-danger btn-sm flex-1" onclick="rejectDriver(\'' + d.id + '\')">Reject</button><button class="btn btn-primary btn-sm flex-1" ' + (!allDocs ? 'disabled' : '') + ' onclick="approveDriver(\'' + d.id + '\')">Approve</button></div></div>';
  }).join('');
}

function approveDriver(id) {drivers = drivers.map(function (d) {return d.id === id ? Object.assign({}, d, { status: 'approved' }) : d;});renderApprovals();}
function rejectDriver(id) {drivers = drivers.map(function (d) {return d.id === id ? Object.assign({}, d, { status: 'rejected' }) : d;});renderApprovals();}

function renderLiveActivity() {
  var el = document.getElementById('liveActivity');if (!el) return;
  var activities = [{ action: 'New rider registered', user: 'R00005', time: '2 min ago' }, { action: 'Trip started', user: 'T00001', time: '5 min ago' }, { action: 'Driver approved', user: 'D00002', time: '12 min ago' }, { action: 'Reward redeemed', user: 'R00001', time: '25 min ago' }];
  el.innerHTML = activities.map(function (a) {return '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;background:var(--gray-50);border-radius:8px;margin-bottom:8px;"><div><div class="font-medium text-sm">' + a.action + '</div><div class="text-xs text-gray-500 font-mono">' + a.user + '</div></div><div class="text-xs text-gray-400">' + a.time + '</div></div>';}).join('');
}

var userTab = 'riders';
var ridersList = [{ id: 'R00001', name: 'Alex Thompson', email: 'alex.t@university.edu', phone: '+1 (555) 123-4567', status: 'active', points: 1250 }, { id: 'R00002', name: 'Sarah Chen', email: 'sarah.c@university.edu', phone: '+1 (555) 987-6543', status: 'active', points: 850 }, { id: 'R00003', name: 'Mike Johnson', email: 'mike.j@university.edu', phone: '+1 (555) 456-7890', status: 'banned', points: 0 }];
var driversList = [{ id: 'D00001', name: 'James Wilson', email: 'james.w@email.com', status: 'approved', rating: 4.8, vehicle: 'Toyota Camry', plate: 'SGX-1234' }, { id: 'D00002', name: 'Linda Martinez', email: 'linda.m@email.com', status: 'pending', rating: 0, vehicle: 'Honda Civic', plate: 'SGX-5678' }];

function renderUsers() {
  var content = document.getElementById('usersContent');if (!content) return;
  document.querySelectorAll('#userTabs .filter-btn').forEach(function (btn) {btn.classList.remove('active');if (btn.dataset.filter === userTab) {btn.classList.add('active');if (userTab === 'riders') btn.style.cssText = 'background:var(--forest);color:var(--lime);';else if (userTab === 'drivers') btn.style.cssText = 'background:var(--lime);color:var(--black);';else btn.style.cssText = 'background:var(--black);color:var(--white);';} else {btn.style.cssText = '';}btn.onclick = function () {userTab = this.dataset.filter;renderUsers();};});
  if (userTab === 'riders') {
    content.innerHTML = '<div class="hidden md-block" style="overflow-x:auto;"><table class="users-table"><thead><tr><th>Rider ID</th><th>Name</th><th>Contact</th><th>Status</th><th>Green Points</th><th style="text-align:right;">Actions</th></tr></thead><tbody>' + ridersList.map(function (r) {return '<tr><td class="font-mono text-gray-500">' + r.id + '</td><td><div class="flex items-center gap-3"><div class="avatar avatar-sm" style="background:var(--forest);color:var(--lime);">' + r.name.charAt(0) + '</div><strong class="text-sm">' + r.name + '</strong></div></td><td class="text-sm text-gray-600">' + r.email + '</td><td><span class="badge ' + (r.status === 'active' ? 'badge-green' : 'badge-red') + '">' + r.status + '</span></td><td class="font-bold text-forest">' + r.points + '</td><td style="text-align:right;"><button class="btn btn-sm" style="color:' + (r.status === 'active' ? 'var(--red-500)' : 'var(--green-600)') + ';" onclick="toggleRider(\'' + r.id + '\')">' + (r.status === 'active' ? 'Ban' : 'Unban') + '</button></td></tr>';}).join('') + '</tbody></table></div>' + '<div class="md-hide">' + ridersList.map(function (r) {return '<div class="user-mobile-card"><div class="flex justify-between items-start mb-2"><div class="flex items-center gap-2"><div class="avatar avatar-md" style="background:var(--forest);color:var(--lime);">' + r.name.charAt(0) + '</div><div><div class="font-bold text-sm">' + r.name + '</div><div class="text-xs text-gray-500 font-mono">' + r.id + '</div></div></div><button class="btn btn-sm" style="color:' + (r.status === 'active' ? 'var(--red-500)' : 'var(--green-600)') + ';" onclick="toggleRider(\'' + r.id + '\')">' + (r.status === 'active' ? 'Ban' : 'Unban') + '</button></div><div class="flex justify-between items-center"><span class="badge ' + (r.status === 'active' ? 'badge-green' : 'badge-red') + '">' + r.status + '</span><span class="font-bold text-forest text-sm">' + r.points + ' pts</span></div></div>';}).join('') + '</div>';
  } else if (userTab === 'drivers') {
    content.innerHTML = '<div class="hidden md-block" style="overflow-x:auto;"><table class="users-table"><thead><tr><th>Driver ID</th><th>Name</th><th>Vehicle</th><th>Status</th><th>Rating</th><th style="text-align:right;">Actions</th></tr></thead><tbody>' + driversList.map(function (d) {return '<tr><td class="font-mono text-gray-500">' + d.id + '</td><td><div class="flex items-center gap-3"><div class="avatar avatar-sm" style="background:var(--lime);color:var(--black);">' + d.name.charAt(0) + '</div><strong class="text-sm">' + d.name + '</strong></div></td><td class="text-sm text-gray-600"><strong>' + d.vehicle + '</strong><div class="text-xs text-gray-400 font-mono">' + d.plate + '</div></td><td><span class="badge ' + (d.status === 'approved' ? 'badge-green' : 'badge-yellow') + '">' + d.status + '</span></td><td class="font-bold">' + (d.rating > 0 ? d.rating.toFixed(1) : '-') + '</td><td style="text-align:right;"><button class="btn btn-sm" onclick="toggleDriver(\'' + d.id + '\')">' + (d.status === 'approved' ? 'Suspend' : 'Approve') + '</button></td></tr>';}).join('') + '</tbody></table></div>';
  } else {
    content.innerHTML = '<div style="padding:24px;display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;"><div style="border:1px solid var(--gray-200);border-radius:12px;padding:24px;text-align:center;"><div class="avatar avatar-xl" style="background:var(--gray-200);color:var(--gray-400);margin:0 auto 12px;">SA</div><h3 class="font-bold text-lg">System Admin</h3><p class="text-sm text-gray-500 mb-3">admin@ecoride.com</p><div class="flex justify-between text-xs text-gray-400"><span class="font-mono">A00001</span><span>Joined 2023-01-01</span></div></div><div style="border:2px dashed var(--gray-200);border-radius:12px;padding:24px;text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--gray-400);min-height:200px;cursor:pointer;"><div class="avatar avatar-lg" style="background:var(--gray-100);color:var(--gray-400);margin-bottom:12px;">+</div><span class="font-bold">Add New Admin</span></div></div>';
  }
}

function toggleRider(id) {ridersList = ridersList.map(function (r) {return r.id === id ? Object.assign({}, r, { status: r.status === 'active' ? 'banned' : 'active' }) : r;});renderUsers();}
function toggleDriver(id) {driversList = driversList.map(function (d) {return d.id === id ? Object.assign({}, d, { status: d.status === 'approved' ? 'suspended' : 'approved' }) : d;});renderUsers();}

var adminRewards = [{ id: '1', name: '$5 Coffee Voucher', points: 500, category: 'Food & Drink', stock: 50, desc: 'Redeemable at campus cafes' }, { id: '2', name: 'University Bookstore Discount', points: 1000, category: 'Education', stock: 25, desc: '15% off any purchase' }, { id: '3', name: 'Free Bus Pass (1 Day)', points: 800, category: 'Transport', stock: 100, desc: 'Valid for 24 hours' }, { id: '4', name: 'EcoRide T-Shirt', points: 2000, category: 'Merch', stock: 5, desc: 'Limited edition merch' }];

function renderAdminRewards() {
  var grid = document.getElementById('adminRewardsGrid');var stats = document.getElementById('rewardStats');if (!grid) return;
  if (stats) stats.innerHTML = statCardHTML('Total Rewards', adminRewards.length, '', 'neutral', 'black') + statCardHTML('Total Stock', adminRewards.reduce(function (s, r) {return s + r.stock;}, 0), '', 'neutral', 'forest') + statCardHTML('Categories', 4, '', 'neutral', 'black') + statCardHTML('Low Stock', adminRewards.filter(function (r) {return r.stock < 10;}).length, '', 'neutral', 'red');
  grid.innerHTML = adminRewards.map(function (r) {return '<div class="admin-reward-card"><div class="reward-img"><svg width="40" height="40" viewBox="0 0 24 24" fill="none"><line x1="16.5" y1="9.4" x2="7.55" y2="4.24" stroke="currentColor" stroke-width="2"/><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" stroke="currentColor" stroke-width="2" fill="none"/></svg><div class="reward-cat-tag">' + r.category + '</div></div><div class="reward-body"><h3 class="font-bold text-sm mb-1">' + r.name + '</h3><p class="text-xs text-gray-500 mb-3">' + r.desc + '</p><div class="mb-3"><span class="text-xl font-black text-forest">' + r.points + '</span> <span class="text-xs font-bold text-gray-500 uppercase">Points</span></div><div class="flex justify-between text-xs mb-3"><span class="text-gray-500">Stock Available</span><span class="font-bold ' + (r.stock < 10 ? 'text-red-600' : '') + '">' + r.stock + ' left</span></div><div class="flex gap-2"><button class="btn btn-outline btn-sm flex-1">Edit</button><button class="btn btn-sm" style="border:1px solid var(--red-200);color:var(--red-600);" onclick="deleteReward(\'' + r.id + '\')">🗑</button></div></div></div>';}).join('');
}

function deleteReward(id) {adminRewards = adminRewards.filter(function (r) {return r.id !== id;});renderAdminRewards();}

function initRewardForm() {
  var form = document.getElementById('rewardForm');if (!form) return;
  form.addEventListener('submit', function (e) {e.preventDefault();var data = new FormData(this);adminRewards.push({ id: Date.now().toString(), name: data.get('name'), points: parseInt(data.get('points')), category: data.get('category'), stock: parseInt(data.get('stock')), desc: data.get('description') || '' });renderAdminRewards();closeModal('rewardModal');this.reset();});
}

var logs = [{ id: 'L001', time: '2024-01-15 14:32:15', type: 'ride_request', action: 'Request Created', user: 'R00001 (Alex)', details: 'Trip T00001, 2 seats', status: 'pending' }, { id: 'L002', time: '2024-01-15 14:30:42', type: 'trip', action: 'Trip Started', user: 'D00001 (James)', details: 'Campus -> Downtown', status: 'success' }, { id: 'L003', time: '2024-01-15 14:28:19', type: 'driver', action: 'Driver Approved', user: 'A00001 (Admin)', details: 'Approved D00002', status: 'success' }, { id: 'L004', time: '2024-01-15 14:25:33', type: 'rating', action: 'Rating Submitted', user: 'R00002 (Sarah)', details: 'Rated D00001: 5 stars', status: 'success' }, { id: 'L005', time: '2024-01-15 14:22:08', type: 'redemption', action: 'Reward Redeemed', user: 'R00001 (Alex)', details: '$5 Voucher', status: 'success' }, { id: 'L006', time: '2024-01-15 14:18:45', type: 'green_point', action: 'Points Earned', user: 'R00001 (Alex)', details: '+50 points', status: 'success' }, { id: 'L007', time: '2024-01-15 14:15:22', type: 'user', action: 'Rider Registered', user: 'R00005', details: 'Email verified', status: 'success' }, { id: 'L008', time: '2024-01-15 14:12:11', type: 'ride_request', action: 'Request Rejected', user: 'D00001 (James)', details: 'Rejected RE00005', status: 'failed' }];
var logFilter = 'all';

function renderLogs() {
  var tabsEl = document.getElementById('logTabs');var tableEl = document.getElementById('logsTable');var countEl = document.getElementById('logCount');if (!tabsEl || !tableEl) return;
  var tabs = [{ id: 'all', label: 'All Logs' }, { id: 'trip', label: 'Trips' }, { id: 'ride_request', label: 'Requests' }, { id: 'rating', label: 'Ratings' }, { id: 'green_point', label: 'Points' }, { id: 'redemption', label: 'Redemptions' }];
  tabsEl.innerHTML = tabs.map(function (t) {return '<button class="log-tab ' + (logFilter === t.id ? 'active' : '') + '" onclick="setLogFilter(\'' + t.id + '\')">' + t.label + '</button>';}).join('');
  var filtered = logs.filter(function (l) {return logFilter === 'all' || l.type === logFilter;});
  var searchVal = (document.getElementById('logSearch') || {}).value || '';
  if (searchVal) filtered = filtered.filter(function (l) {return (l.user + l.action + l.details).toLowerCase().indexOf(searchVal.toLowerCase()) >= 0;});
  tableEl.innerHTML = '<div class="hidden md-block"><table class="users-table"><thead><tr><th>Time</th><th>Type</th><th>Action</th><th>User</th><th>Details</th><th>Status</th></tr></thead><tbody>' + filtered.map(function (l) {var statusClass = l.status === 'success' ? 'badge-green' : l.status === 'failed' ? 'badge-red' : 'badge-yellow';return '<tr><td class="font-mono text-xs text-gray-500">' + l.time + '</td><td><span class="badge badge-gray">' + l.type + '</span></td><td class="font-bold text-sm">' + l.action + '</td><td class="text-sm">' + l.user + '</td><td class="text-sm text-gray-600">' + l.details + '</td><td><span class="badge ' + statusClass + '">' + l.status + '</span></td></tr>';}).join('') + '</tbody></table></div>' + '<div class="md-hide">' + filtered.map(function (l) {return '<div class="log-row" style="flex-wrap:wrap;gap:4px;"><div style="flex:1;min-width:200px;"><div class="font-bold text-sm">' + l.action + '</div><div class="text-xs text-gray-500">' + l.user + ' &bull; ' + l.details + '</div></div><div class="text-xs text-gray-400">' + l.time.split(' ')[1] + '</div></div>';}).join('') + '</div>';
  if (countEl) countEl.textContent = 'Showing ' + filtered.length + ' of ' + logs.length + ' logs';
  var searchInput = document.getElementById('logSearch');if (searchInput && !searchInput.dataset.bound) {searchInput.dataset.bound = 'true';searchInput.addEventListener('input', function () {renderLogs();});}
}

function setLogFilter(f) {logFilter = f;renderLogs();}

function renderAnalytics() {
  var stats = document.getElementById('analyticsStats');var charts = document.getElementById('analyticsCharts');if (!stats || !charts) return;
  stats.innerHTML = statCardHTML('Total Trips', '1,248', '+12% this month', 'positive', 'lime') + statCardHTML('New Users', '356', '+24% this month', 'positive', 'forest') + statCardHTML('Trip Value', '$45.2k', 'Total volume', 'neutral', 'black') + statCardHTML('Green Points', '850k', 'Awarded total', 'positive', 'lime');
  var barHTML = function (data, color, title, icon) {return '<div class="chart-card"><h3>' + (icon || '') + ' ' + title + '</h3><div class="bar-chart">' + data.map(function (d) {return '<div class="bar-col"><div class="bar-track"><div class="bar-fill" style="height:' + d.pct + '%;background:' + (d.highlight ? 'var(--lime)' : 'var(--forest)') + ';"><span class="bar-value">' + d.value + '</span></div></div><span class="bar-label">' + d.label + '</span></div>';}).join('') + '</div></div>';};
  charts.innerHTML = barHTML([{ label: 'Jul', pct: 35, value: '$3.2k' }, { label: 'Aug', pct: 48, value: '$4.5k' }, { label: 'Sep', pct: 55, value: '$5.1k' }, { label: 'Oct', pct: 62, value: '$5.8k' }, { label: 'Nov', pct: 78, value: '$7.2k' }, { label: 'Dec', pct: 95, value: '$8.9k', highlight: true }], 'forest', 'Monthly Earnings', '💰') + barHTML([{ label: 'Jul', pct: 30, value: '142' }, { label: 'Aug', pct: 45, value: '215' }, { label: 'Sep', pct: 55, value: '264' }, { label: 'Oct', pct: 65, value: '310' }, { label: 'Nov', pct: 80, value: '382' }, { label: 'Dec', pct: 95, value: '456', highlight: true }], 'forest', 'User Growth') + '<div class="chart-card"><h3>⭐ Ratings Breakdown</h3>' + [{ stars: 5, pct: 70 }, { stars: 4, pct: 20 }, { stars: 3, pct: 5 }, { stars: 2, pct: 3 }, { stars: 1, pct: 2 }].map(function (r) {return '<div class="rating-row"><span class="font-bold" style="width:16px;">' + r.stars + '</span><div class="rating-bar"><div class="rating-fill" style="width:' + r.pct + '%;"></div></div><span class="text-xs text-gray-500" style="width:40px;text-align:right;">' + r.pct + '%</span></div>';}).join('') + '</div>' + '<div class="chart-card"><h3>🌿 Top Green Point Earners</h3>' + [{ name: 'Sarah Chen', pts: 12500, rank: 1 }, { name: 'Mike Johnson', pts: 10200, rank: 2 }, { name: 'Alex Thompson', pts: 9800, rank: 3 }, { name: 'Lisa Park', pts: 8500, rank: 4 }].map(function (u) {return '<div class="leaderboard-row"><div class="flex items-center gap-2"><span class="avatar avatar-sm" style="background:' + (u.rank === 1 ? 'var(--yellow-100)' : 'var(--gray-200)') + ';color:' + (u.rank === 1 ? 'var(--yellow-700)' : 'var(--gray-600)') + ';font-size:11px;">' + u.rank + '</span><span class="font-bold text-sm">' + u.name + '</span></div><span class="font-mono font-bold text-forest text-sm">' + u.pts.toLocaleString() + ' pts</span></div>';}).join('') + '</div>';
}

var pointMultiplier = 2.5;
var pointSettings = { basePerTrip: 50, perKm: 5, sharingBonus: 25, referralBonus: 100 };

function renderSettings() {
  var card = document.getElementById('multiplierCard');var form = document.getElementById('pointRulesForm');if (!card) return;
  var presets = [1.0, 1.5, 2.0, 2.5, 3.0, 5.0];
  card.innerHTML = '<div style="position:relative;z-index:1;"><div class="flex items-center gap-2 mb-4"><h3 class="font-bold text-lg uppercase">Green Points Multiplier</h3></div><div class="multiplier-value">' + pointMultiplier.toFixed(1) + 'x</div><p style="color:rgba(0,0,0,0.7);font-size:14px;margin-bottom:24px;">Riders earn <strong>' + pointMultiplier + 'x points</strong> for every trip today</p><div id="multiplierEditor" style="display:none;"><div class="form-group"><label class="form-label" style="color:var(--black);">Custom Multiplier</label><input type="number" step="0.1" min="1" max="10" value="' + pointMultiplier + '" class="form-input" id="multiplierInput"></div><div class="form-group"><label class="form-label" style="color:var(--black);">Quick Presets</label><div class="preset-grid">' + presets.map(function (p) {return '<button type="button" class="preset-btn ' + (pointMultiplier === p ? 'active' : '') + '" onclick="setMultiplier(' + p + ')">' + p + 'x</button>';}).join('') + '</div></div><div class="flex gap-2"><button class="btn flex-1" style="background:rgba(255,255,255,0.2);" onclick="cancelMultiplier()">Cancel</button><button class="btn flex-1" style="background:var(--black);color:var(--lime);" onclick="saveMultiplier()">Save Changes</button></div></div><button class="btn btn-full" style="background:var(--black);color:var(--lime);" id="editMultiplierBtn" onclick="document.getElementById(\'multiplierEditor\').style.display=\'block\';this.style.display=\'none\';">Adjust Multiplier</button></div>';
  if (form) {form.innerHTML = '<h3 class="font-bold mb-4 uppercase flex items-center gap-2"><span data-icon="zap" data-icon-size="18" style="color:var(--forest);"></span> Point Earning Rules</h3><div class="form-group"><label class="form-label">Base Points Per Trip</label><input type="number" class="form-input" value="' + pointSettings.basePerTrip + '" onchange="pointSettings.basePerTrip=parseInt(this.value);renderCalcPreview();"></div><div class="form-group"><label class="form-label">Points Per Kilometer</label><input type="number" class="form-input" value="' + pointSettings.perKm + '" onchange="pointSettings.perKm=parseInt(this.value);renderCalcPreview();"></div><div class="form-group"><label class="form-label">Sharing Bonus</label><input type="number" class="form-input" value="' + pointSettings.sharingBonus + '" onchange="pointSettings.sharingBonus=parseInt(this.value);renderCalcPreview();"></div><div class="form-group"><label class="form-label">Referral Bonus</label><input type="number" class="form-input" value="' + pointSettings.referralBonus + '" onchange="pointSettings.referralBonus=parseInt(this.value);renderCalcPreview();"></div><button class="btn btn-primary btn-full" onclick="console.log(\'Settings saved:\',pointSettings);">Save Settings</button>';if (typeof initIcons === 'function') initIcons();}
  renderCalcPreview();
}

function renderCalcPreview() {
  var preview = document.getElementById('calcPreview');if (!preview) return;
  var sub = pointSettings.basePerTrip + pointSettings.perKm * 10 + pointSettings.sharingBonus;
  preview.innerHTML = '<h3 class="font-bold text-lg mb-4">Calculation Example</h3><div class="calc-row"><span class="label">Base Trip Points:</span><span class="font-bold">' + pointSettings.basePerTrip + '</span></div><div class="calc-row"><span class="label">Distance (10 km):</span><span class="font-bold">' + pointSettings.perKm * 10 + '</span></div><div class="calc-row"><span class="label">Sharing Bonus:</span><span class="font-bold">' + pointSettings.sharingBonus + '</span></div><div class="calc-row" style="border-top:1px solid rgba(255,255,255,0.2);padding-top:8px;margin-top:8px;"><span class="label">Subtotal:</span><span class="font-bold">' + sub + '</span></div><div class="calc-row" style="color:var(--lime);"><span class="font-bold">With ' + pointMultiplier + 'x Multiplier:</span><span class="font-black text-xl">' + Math.round(sub * pointMultiplier) + '</span></div>';
}

function setMultiplier(val) {pointMultiplier = val;document.getElementById('multiplierInput').value = val;renderSettings();}
function saveMultiplier() {pointMultiplier = parseFloat(document.getElementById('multiplierInput').value);renderSettings();}
function cancelMultiplier() {renderSettings();}

function renderAdminProfile() {
  var stats = document.getElementById('adminProfileStats');var activity = document.getElementById('adminRecentActivity');var notifs = document.getElementById('notifPrefs');if (!stats) return;
  stats.innerHTML = '<div class="stat-card"><div class="text-2xl font-black">156</div><div class="text-xs text-gray-500 font-bold uppercase">Actions Taken</div></div><div class="stat-card"><div class="text-2xl font-black text-forest">48</div><div class="text-xs text-gray-500 font-bold uppercase">Drivers Approved</div></div><div class="stat-card"><div class="text-2xl font-black">12</div><div class="text-xs text-gray-500 font-bold uppercase">Rewards Created</div></div>';
  if (activity) {var actions = [{ action: 'Approved driver D00002', time: '12 min ago', type: 'approval' }, { action: 'Updated point multiplier to 2.5x', time: '1 hour ago', type: 'settings' }, { action: 'Added new reward: Coffee Voucher', time: '3 hours ago', type: 'reward' }, { action: 'Banned user R00003', time: 'Yesterday', type: 'moderation' }];var typeColors = { approval: 'background:var(--green-100);color:var(--green-700);', settings: 'background:var(--blue-100);color:var(--blue-700);', reward: 'background:var(--purple-100);color:var(--purple-700);', moderation: 'background:var(--red-100);color:var(--red-700);' };activity.innerHTML = '<div style="padding:16px;border-bottom:1px solid var(--gray-100);"><h3 class="font-bold uppercase flex items-center gap-2"><span data-icon="clock" data-icon-size="18" style="color:var(--gray-400);"></span> Recent Activity</h3></div><div class="divide-y">' + actions.map(function (a) {return '<div style="padding:12px 16px;display:flex;align-items:center;justify-content:space-between;"><div class="flex items-center gap-3"><span class="badge" style="' + (typeColors[a.type] || '') + '">' + a.type + '</span><span class="text-sm font-medium">' + a.action + '</span></div><span class="text-xs text-gray-400">' + a.time + '</span></div>';}).join('') + '</div>';}
  if (notifs) {var prefs = [{ label: 'New driver applications', desc: 'Get notified when drivers apply', on: true }, { label: 'Low reward stock', desc: 'Alert when stock drops below 10', on: true }, { label: 'System alerts', desc: 'Critical platform notifications', on: true }, { label: 'Weekly reports', desc: 'Summary email every Monday', on: false }];notifs.innerHTML = '<h3 class="font-bold uppercase mb-4 flex items-center gap-2"><span data-icon="bell" data-icon-size="18" style="color:var(--gray-400);"></span> Notification Preferences</h3>' + prefs.map(function (p) {return '<div class="notif-row"><div><div class="font-bold text-sm">' + p.label + '</div><div class="text-xs text-gray-500">' + p.desc + '</div></div><div class="toggle ' + (p.on ? 'active' : '') + '" onclick="this.classList.toggle(\'active\');"><div class="toggle-knob"></div></div></div>';}).join('');}
}