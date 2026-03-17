/* Comove – Friends JS */
var currentDeleteId = '';
var currentViewFriend = null;
var friendsCache = [];

function initRiderFriends() {
  loadFriends();
}

function switchFriendTab(el, tab) {
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  ['my-friends','requests'].forEach(function(t) {
    var s = document.getElementById('tab-' + t);
    if (s) s.style.display = (t === tab) ? 'block' : 'none';
  });
}

function searchFriends() {
  var q = document.getElementById('friendSearch').value.trim().toLowerCase();
  if (!q) {
    renderFriends(friendsCache);
    return;
  }

  var filtered = friendsCache.filter(function(friend) {
    return friend.name.toLowerCase().indexOf(q) !== -1 || friend.student_id.toLowerCase().indexOf(q) !== -1;
  });
  renderFriends(filtered);
}

async function loadFriends() {
  try {
    var data = await apiGet('api/friends.php');
    friendsCache = data.friends || [];
    renderFriends(friendsCache);
    renderPending(data.pending || []);
  } catch (err) {
    showToast('⚠️ Unable to load friends');
  }
}

function renderFriends(friends) {
  var tab = document.getElementById('tab-my-friends');
  document.getElementById('friendCount').textContent = friends.length;

  if (!friends.length) {
    tab.innerHTML = '<div class="form-card">No friends found yet.</div>';
    return;
  }

  tab.innerHTML = friends.map(function(friend) {
    return '<div class="friend-card" id="friend-' + friend.friend_id + '">'
      + '<div class="friend-ava"><img src="' + escapeHtml(friend.photo_url) + '" alt="' + escapeHtml(friend.name) + ' profile photo"></div>'
      + '<div class="friend-info">'
      + '<div class="friend-name">' + escapeHtml(friend.name) + '</div>'
      + '<div class="friend-meta">🚗 ' + escapeHtml(friend.role) + ' · ' + friend.trips_together + ' trips together</div>'
      + '</div>'
      + '<div style="display:flex;gap:8px;align-items:center;">'
      + '<div class="friend-pts">' + friend.green_points + ' pts</div>'
      + '<button class="btn-sm primary" onclick="viewFriendById(' + friend.friend_id + ')">View</button>'
      + '<button class="btn-sm ghost" style="color:var(--danger);border-color:rgba(239,68,68,0.3);" onclick="confirmDelete(' + friend.friend_id + ')">✕</button>'
      + '</div>'
      + '</div>';
  }).join('');
}

function renderPending(pending) {
  var tab = document.getElementById('tab-requests');
  document.getElementById('friendRequestCount').textContent = pending.length;

  if (!pending.length) {
    tab.innerHTML = '<div class="form-card">No pending requests right now.</div>';
    return;
  }

  tab.innerHTML = pending.map(function(friend) {
    return '<div class="friend-card">'
      + '<div class="friend-ava"><img src="' + escapeHtml(friend.photo_url) + '" alt="' + escapeHtml(friend.name) + ' profile photo"></div>'
      + '<div class="friend-info"><div class="friend-name">' + escapeHtml(friend.name) + '</div><div class="friend-meta">' + escapeHtml(friend.meta) + '</div></div>'
      + '<div style="display:flex;gap:8px;">'
      + '<button class="btn-sm primary" onclick="acceptFriend(\'' + escapeHtml(friend.name) + '\')">Accept</button>'
      + '<button class="btn-sm ghost" onclick="showToast(\'Request declined\')">Decline</button>'
      + '</div>'
      + '</div>';
  }).join('');
}

function viewFriendById(friendId) {
  var friend = friendsCache.find(function(item) { return item.friend_id === friendId; });
  if (!friend) return;
  currentViewFriend = friend;
  document.getElementById('fdAvaImg').src = friend.photo_url;
  document.getElementById('fdName').textContent = friend.name;
  document.getElementById('fdRole').textContent = '🚗 ' + friend.role;
  document.getElementById('fdID').textContent = friend.student_id;
  document.getElementById('fdIntake').textContent = friend.intake;
  document.getElementById('fdPhone').textContent = friend.phone_number || '-';
  document.getElementById('fdTrips').textContent = friend.trips_together + ' trips';
  document.getElementById('fdPts').textContent = friend.green_points + ' pts';
  document.getElementById('friendModal').classList.add('open');
}

function closeFriendModal() {
  document.getElementById('friendModal').classList.remove('open');
}

function deleteFriendFromModal() {
  closeFriendModal();
  if (currentViewFriend) confirmDelete(currentViewFriend.friend_id);
}

function confirmDelete(friendId) {
  currentDeleteId = friendId;
  var friend = friendsCache.find(function(item) { return item.friend_id === friendId; });
  document.getElementById('deleteModalSub').textContent = 'Remove ' + (friend ? friend.name : 'this friend') + ' from your friends list?';
  document.getElementById('deleteModal').classList.add('open');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('open');
}

function doDelete() {
  closeDeleteModal();
  var card = document.getElementById('friend-' + currentDeleteId);
  friendsCache = friendsCache.filter(function(item) { return item.friend_id !== currentDeleteId; });
  if (card) {
    card.style.transition = 'opacity 0.3s, transform 0.3s';
    card.style.opacity = '0';
    card.style.transform = 'translateX(30px)';
    setTimeout(function() {
      renderFriends(friendsCache);
      showToast('✅ Friend removed');
    }, 300);
    return;
  }
  renderFriends(friendsCache);
}

function acceptFriend(name) {
  showToast('✅ ' + name + ' added as friend!');
}

document.addEventListener('DOMContentLoaded', function() {
  ['friendModal','deleteModal'].forEach(function(id) {
    var m = document.getElementById(id);
    if (m) m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
  });
  initRiderFriends();
});
