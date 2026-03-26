/* Comove – Friends JS */
var currentDeleteId = '';
var currentViewFriend = null;
var friendsCache = [];
var pendingFriendsCache = [];
var searchResultsCache = [];

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
    renderSearchResults([]);
    renderFriends(friendsCache);
    return;
  }

  loadFriendSearch(q);
}

async function loadFriends() {
  try {
    var data = await apiGet('api/friends.php');
    friendsCache = data.friends || [];
    pendingFriendsCache = data.pending || [];
    renderFriends(friendsCache);
    renderPending(pendingFriendsCache);
  } catch (err) {
    showToast('⚠️ Unable to load friends');
  }
}

async function loadFriendSearch(query) {
  try {
    var data = await apiGet('api/friends.php?q=' + encodeURIComponent(query));
    renderSearchResults(data.results || []);
  } catch (err) {
    showToast('⚠️ Unable to search riders');
  }
}

function renderSearchResults(results) {
  var panel = document.getElementById('friendSearchResults');
  searchResultsCache = results;
  if (!panel) return;

  if (!results.length) {
    panel.innerHTML = '';
    return;
  }

  panel.innerHTML = '<div class="section-title" style="margin-top:18px;">Search Results</div>' + results.map(function(rider) {
    return '<div class="friend-card">'
      + '<div class="friend-ava"><img src="' + escapeHtml(rider.photo_url) + '" alt="' + escapeHtml(rider.name) + ' profile photo"></div>'
      + '<div class="friend-info"><div class="friend-name">' + escapeHtml(rider.name) + '</div><div class="friend-meta">' + escapeHtml(rider.student_id) + ' · ' + escapeHtml(rider.meta) + '</div></div>'
      + '<div style="display:flex;gap:8px;">'
      + '<button class="btn-sm primary" onclick="sendFriendRequest(' + rider.rider_id + ')">Add Friend</button>'
      + '</div>'
      + '</div>';
  }).join('');
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
      + '<button class="btn-sm primary" onclick="acceptFriendById(' + friend.friend_id + ')">Accept</button>'
      + '<button class="btn-sm ghost" onclick="declineFriendById(' + friend.friend_id + ')">Decline</button>'
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

async function doDelete() {
  closeDeleteModal();
  var friendId = currentDeleteId;
  var card = document.getElementById('friend-' + friendId);
  var formData = new FormData();
  formData.append('action', 'remove');
  formData.append('friend_id', String(friendId));

  try {
    await apiPost('api/friends.php', formData);
    friendsCache = friendsCache.filter(function(item) { return item.friend_id !== friendId; });
    currentViewFriend = currentViewFriend && currentViewFriend.friend_id === friendId ? null : currentViewFriend;
    if (card) {
      card.style.transition = 'opacity 0.3s, transform 0.3s';
      card.style.opacity = '0';
      card.style.transform = 'translateX(30px)';
      setTimeout(function() {
        renderFriends(friendsCache);
      }, 300);
    } else {
      renderFriends(friendsCache);
    }
    showToast('✅ Friend removed');
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

async function acceptFriendById(friendId) {
  var friend = pendingFriendsCache.find(function(item) { return item.friend_id === friendId; });
  var name = friend ? friend.name : 'Friend';
  var formData = new FormData();
  formData.append('action', 'accept');
  formData.append('friend_id', String(friendId));

  try {
    await apiPost('api/friends.php', formData);
    await loadFriends();
    showToast('✅ ' + name + ' added as friend!');
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

async function declineFriendById(friendId) {
  var friend = pendingFriendsCache.find(function(item) { return item.friend_id === friendId; });
  var name = friend ? friend.name : 'Friend';
  var formData = new FormData();
  formData.append('action', 'decline');
  formData.append('friend_id', String(friendId));

  try {
    await apiPost('api/friends.php', formData);
    await loadFriends();
    showToast('✅ ' + name + ' declined');
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

async function sendFriendRequest(riderId) {
  var rider = searchResultsCache.find(function(item) { return item.rider_id === riderId; });
  var name = rider ? rider.name : 'this rider';
  var formData = new FormData();
  formData.append('action', 'request');
  formData.append('target_rider_id', String(riderId));

  try {
    await apiPost('api/friends.php', formData);
    renderSearchResults([]);
    document.getElementById('friendSearch').value = '';
    showToast('✅ Friend request sent to ' + name);
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }
}

document.addEventListener('DOMContentLoaded', function() {
  ['friendModal','deleteModal'].forEach(function(id) {
    var m = document.getElementById(id);
    if (m) m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
  });
  initRiderFriends();
});
