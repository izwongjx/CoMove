(function () {
  var current = window.location.pathname.split('/').pop();
  document.querySelectorAll('.nav-item').forEach(function (link) {
    var href = link.getAttribute('href');
    if (href && href === current) {
      link.classList.add('active');
    }
  });
})();

/* ── TABLE FILTER ── */
function filterTable(tableId, query) {
  var table = document.getElementById(tableId);
  if (!table) return;
  var lq = query.toLowerCase();
  table.querySelectorAll('tbody tr').forEach(function (row) {
    row.style.display = row.textContent.toLowerCase().indexOf(lq) >= 0 ? '' : 'none';
  });
}

/* ── SUB-TABS ──
   Usage: switchTab('prefix-', ['key1','key2'], activeKey)
   Each tab button must have id = prefix + key
   Each content div must have id = prefix + 'sub-' + key
*/
function switchTab(btnPrefix, subPrefix, keys, active) {
  keys.forEach(function (k) {
    var btn = document.getElementById(btnPrefix + k);
    var sub = document.getElementById(subPrefix + k);
    if (btn) btn.className = (k === active) ? 'sub-tab active' : 'sub-tab';
    if (sub) sub.style.display = (k === active) ? '' : 'none';
  });
}

/* ── STATUS CONTROL (Rider / Driver) ── */
function setStatus(cellId, status) {
  var cell = document.getElementById(cellId);
  if (!cell) return;
  if (status === 'Active') {
    cell.innerHTML = '<span class="badge b-lime"><span class="bdot"></span> Active</span>';
    toast('Status set to Active', 'success');
  } else {
    cell.innerHTML = '<span class="badge b-red"><span class="bdot"></span> Banned</span>';
    toast('Account banned', 'warn');
  }
}

/* ── DRIVER APPLICATION DECISION ── */
function decideApp(id, decision) {
  var statCell = document.getElementById('pstat-' + id);
  if (!statCell) return;
  if (decision === 'approve') {
    statCell.innerHTML = '<span class="badge b-lime"><span class="bdot"></span> Approved</span>';
    toast('Driver application approved', 'success');
  } else {
    statCell.innerHTML = '<span class="badge b-red"><span class="bdot"></span> Rejected</span>';
    toast('Driver application rejected', 'info');
  }
  var row = document.getElementById('prow-' + id);
  if (row) {
    row.querySelectorAll('.action-btns button').forEach(function (b) {
      b.disabled = true;
      b.style.opacity = '0.4';
    });
  }
}

/* ── MODALS ── */
function openModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  var el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });
});

/* ── CONFIRM MODAL ── */
function confirmAction(message, action, target) {
  var textEl = document.getElementById('confirmText');
  var btnEl  = document.getElementById('confirmBtn');
  if (!textEl || !btnEl) return;
  textEl.textContent = message;
  btnEl.onclick = function () {
    if (action === 'deleteUser') {
      var row = document.querySelector('[data-uid="' + target + '"]');
      if (row) row.remove();
      toast('User deleted', 'success');
    } else if (action === 'deleteReward') {
      var r = document.getElementById(target);
      if (r) r.remove();
      toast('Reward deleted', 'success');
    } else {
      toast('Action completed', 'success');
    }
    closeModal('confirmModal');
  };
  openModal('confirmModal');
}

/* ── TOAST ── */
function toast(message, type) {
  type = type || 'info';
  var colors = { success: 'var(--lime)', error: 'var(--danger)', info: 'var(--info)', warn: 'var(--warn)' };
  var icons  = { success: '✓', error: '✕', info: 'ℹ', warn: '⚠' };
  var container = document.getElementById('toastContainer');
  if (!container) return;
  var el = document.createElement('div');
  el.className = 'toast ' + type;
  el.innerHTML = '<span style="color:' + colors[type] + ';font-weight:900;font-size:14px">' + icons[type] + '</span> ' + message;
  container.appendChild(el);
  setTimeout(function () { el.remove(); }, 3000);
}

/* ── MINI TRIP CHART (used on dashboard) ── */
function buildTripChart(canvasId, data) {
  var container = document.getElementById(canvasId);
  if (!container) return;
  var max = Math.max.apply(null, data);
  data.forEach(function (v) {
    var bar = document.createElement('div');
    bar.className = 'bar';
    bar.style.height = (v / max * 100) + '%';
    bar.style.background = v === max ? 'var(--lime)' : 'rgba(200,241,53,0.38)';
    bar.title = v + ' trips';
    container.appendChild(bar);
  });
}

/* ── GREEN POINT MULTIPLIER (used on settings) ── */
function updateMultiplier(val) {
  val = parseFloat(val);
  var baseEl = document.getElementById('basePoints');
  var base = baseEl ? (parseInt(baseEl.value) || 100) : 100;
  var result = Math.round(base * val);
  var display  = document.getElementById('multiplierDisplay');
  var calcMult = document.getElementById('calcMultiplier');
  var calcRes  = document.getElementById('calcResult');
  if (display)  display.textContent  = val.toFixed(1) + '×';
  if (calcMult) calcMult.textContent = val.toFixed(1);
  if (calcRes)  calcRes.textContent  = result + ' pts / ride';
}
function setMultiplierPreset(v) {
  var range = document.getElementById('multiplierRange');
  if (range) range.value = v;
  updateMultiplier(v);
}

/* ── USER MODAL (used on users.html) ── */
function toggleDriverFields(role) {
  var licenseGroup = document.getElementById('licenseGroup');
  var vehicleGroup = document.getElementById('vehicleGroup');
  var show = role === 'Driver';
  if (licenseGroup) licenseGroup.style.display = show ? '' : 'none';
  if (vehicleGroup) vehicleGroup.style.display = show ? '' : 'none';
}

var userData = {
  'U001': { name: 'Aisha Mahmoud', email: 'aisha@email.com',     phone: '+60 12-345 6789', role: 'Rider',  status: 'Active',    license: '',            vehicle: '' },
  'U002': { name: 'Jay Tan',       email: 'jay.tan@email.com',   phone: '+60 11-222 3344', role: 'Driver', status: 'Banned',    license: 'WXY 4521',    vehicle: 'Toyota Vios 2021' },
  'U003': { name: 'Siti Rahmah',   email: 'siti.r@email.com',    phone: '+60 13-999 0011', role: 'Rider',  status: 'Active',    license: '',            vehicle: '' },
  'U004': { name: 'Kevin Ng',      email: 'kevin.ng@email.com',  phone: '+60 19-444 5566', role: 'Rider',  status: 'Active',    license: '',            vehicle: '' },
  'U005': { name: 'Hafiz Yusof',   email: 'hafiz.y@email.com',   phone: '+60 17-888 9900', role: 'Driver', status: 'Active',    license: 'PBG 2244',    vehicle: 'Perodua Myvi 2020' }
};

function openUserModal(uid) {
  var titleEl = document.getElementById('userModalTitle');
  var editId  = document.getElementById('editUserId');
  if (titleEl) titleEl.textContent = uid ? 'Edit User' : 'Add User';
  if (editId)  editId.value = uid || '';
  var d = (uid && userData[uid]) ? userData[uid] : { name: '', email: '', phone: '', role: 'Rider', status: 'Active', license: '', vehicle: '' };
  var fields = { uName: d.name, uEmail: d.email, uPhone: d.phone, uRole: d.role, uStatus: d.status, uLicense: d.license || '', uVehicle: d.vehicle || '' };
  Object.keys(fields).forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.value = fields[id];
  });
  toggleDriverFields(d.role);
  openModal('userModal');
}

function saveUser() {
  var name   = (document.getElementById('uName')   || {}).value || '';
  var email  = (document.getElementById('uEmail')  || {}).value || '';
  var phone  = (document.getElementById('uPhone')  || {}).value || '';
  var role   = (document.getElementById('uRole')   || {}).value || 'Rider';
  var status = (document.getElementById('uStatus') || {}).value || 'Active';
  var uid    = (document.getElementById('editUserId') || {}).value || '';
  name = name.trim(); email = email.trim();
  if (!name || !email) { toast('Name and email are required', 'error'); return; }

  var roleB    = role === 'Rider' ? 'b-blue' : 'b-purple';
  var statB    = status === 'Active' ? 'b-lime' : status === 'Banned' ? 'b-red' : 'b-yellow';
  var defaultAvatar = '../../public-assets/images/profile-icon.png';
  var avatarHTML = '<div class="user-cell"><img class="user-avatar" src="' + defaultAvatar + '" alt="User avatar" onerror="this.src=\'' + defaultAvatar + '\'"><div><div class="user-name">' + name + '</div><div class="user-sub">#' + uid + '</div></div></div>';

  if (uid && document.querySelector('[data-uid="' + uid + '"]')) {
    var row = document.querySelector('[data-uid="' + uid + '"]');
    row.cells[0].innerHTML = avatarHTML;
    row.cells[1].textContent = email;
    row.cells[2].textContent = phone;
    row.cells[3].innerHTML = '<span class="badge ' + roleB + '">' + role + '</span>';
    row.cells[4].innerHTML = '<span class="badge ' + statB + '"><span class="bdot"></span> ' + status + '</span>';
    toast('User updated', 'success');
  } else {
    var newId = 'U' + Date.now().toString().slice(-4);
    var tbody = document.getElementById('allUsersBody');
    if (tbody) {
      var tr = document.createElement('tr');
      tr.setAttribute('data-uid', newId);
      tr.innerHTML = '<td>' + avatarHTML.replace('#' + uid, '#' + newId) + '</td><td>' + email + '</td><td>' + phone + '</td><td><span class="badge ' + roleB + '">' + role + '</span></td><td><span class="badge ' + statB + '"><span class="bdot"></span> ' + status + '</span></td><td><div class="action-btns"><button class="btn btn-xs" onclick="openUserModal(\'' + newId + '\')">Edit</button><button class="btn btn-xs btn-danger" onclick="confirmAction(\'Delete ' + name + '?\',\'deleteUser\',\'' + newId + '\')">Delete</button></div></td>';
      tbody.appendChild(tr);
    }
    toast('User created', 'success');
  }
  closeModal('userModal');
}

/* ── REWARD MODAL (used on rewards.html) ── */
function openRewardModal(rid) {
  var titleEl = document.getElementById('rewardModalTitle');
  var editId  = document.getElementById('editRewardId');
  if (titleEl) titleEl.textContent = rid ? 'Edit Reward' : 'Add Reward Item';
  if (editId)  editId.value = rid || '';
  if (rid) {
    var row = document.getElementById(rid);
    if (row) {
      var nameEl   = document.getElementById('rwName');
      var descEl   = document.getElementById('rwDesc');
      var pointsEl = document.getElementById('rwPoints');
      var stockEl  = document.getElementById('rwStock');
      if (nameEl)   nameEl.value   = row.cells[0].querySelector('strong').textContent;
      if (descEl)   descEl.value   = row.cells[1].textContent.trim();
      if (pointsEl) pointsEl.value = row.cells[2].textContent.replace(/[^0-9]/g, '');
      if (stockEl)  stockEl.value  = row.cells[3].textContent.replace(/[^0-9]/g, '');
    }
  } else {
    ['rwName', 'rwDesc', 'rwPoints', 'rwStock'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) el.value = '';
    });
    var availEl = document.getElementById('rwAvail');
    if (availEl) availEl.value = 'available';
  }
  openModal('rewardModal');
}

function saveReward() {
  var name  = ((document.getElementById('rwName')   || {}).value || '').trim();
  var desc  = ((document.getElementById('rwDesc')   || {}).value || '').trim();
  var pts   = ((document.getElementById('rwPoints') || {}).value || '').trim();
  var stock = ((document.getElementById('rwStock')  || {}).value || '').trim();
  var avail = ((document.getElementById('rwAvail')  || {}).value || 'available');
  var rid   = ((document.getElementById('editRewardId') || {}).value || '');
  if (!name || !pts) { toast('Name and points are required', 'error'); return; }

  var stockNum = parseInt(stock) || 0;
  var availBadge = stockNum === 0
    ? '<span class="badge b-gray"><span class="bdot"></span> Out of Stock</span>'
    : avail === 'available'
      ? '<span class="badge b-lime"><span class="bdot"></span> Available</span>'
      : '<span class="badge b-gray"><span class="bdot"></span> Unavailable</span>';

  if (rid && document.getElementById(rid)) {
    var row = document.getElementById(rid);
    row.cells[0].innerHTML = '<strong>' + name + '</strong>';
    row.cells[1].innerHTML = '<span style="font-size:12px;color:var(--gray-400)">' + desc + '</span>';
    row.cells[2].textContent = parseInt(pts).toLocaleString();
    row.cells[3].textContent = stockNum;
    row.cells[4].innerHTML = availBadge;
    toast('Reward updated', 'success');
  } else {
    var newRid = 'rw-' + Date.now();
    var tbody = document.getElementById('rewardRows');
    if (tbody) {
      var tr = document.createElement('tr');
      tr.id = newRid;
      tr.innerHTML = '<td><strong>' + name + '</strong></td><td style="font-size:12px;color:var(--gray-400)">' + desc + '</td><td style="font-family:\'DM Mono\',monospace">' + parseInt(pts).toLocaleString() + '</td><td style="font-family:\'DM Mono\',monospace">' + stockNum + '</td><td>' + availBadge + '</td><td><div class="action-btns"><button class="btn btn-xs" onclick="openRewardModal(\'' + newRid + '\')">Edit</button><button class="btn btn-xs btn-danger" onclick="confirmAction(\'Delete this reward?\',\'deleteReward\',\'' + newRid + '\')">Delete</button></div></td>';
      tbody.appendChild(tr);
    }
    toast('Reward item added', 'success');
  }
  closeModal('rewardModal');
}
