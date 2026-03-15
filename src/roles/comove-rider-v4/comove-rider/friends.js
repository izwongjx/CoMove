/* Comove – Friends JS */
var currentDeleteId = '';
var currentViewId = '';

function initRiderFriends() {}

function switchFriendTab(el, tab) {
  document.querySelectorAll('.tab-btn').forEach(function(b){ b.classList.remove('active'); });
  el.classList.add('active');
  ['my-friends','requests'].forEach(function(t) {
    var s = document.getElementById('tab-' + t);
    if (s) s.style.display = (t === tab) ? 'block' : 'none';
  });
}

function searchFriends() {
  var q = document.getElementById('friendSearch').value.trim();
  if (!q) { showToast('⚠️ Please enter a name or student ID'); return; }
  showToast('🔍 Searching for "' + q + '"...');
}

function viewFriend(name, role, id, intake, trips, pts, phone, bg, initials, cardId) {
  currentViewId = cardId;
  document.getElementById('fdAva').textContent = initials;
  document.getElementById('fdAva').style.background = bg;
  document.getElementById('fdName').textContent = name;
  document.getElementById('fdRole').textContent = '🚗 ' + role;
  document.getElementById('fdID').textContent = id;
  document.getElementById('fdIntake').textContent = intake;
  document.getElementById('fdPhone').textContent = phone;
  document.getElementById('fdTrips').textContent = trips + ' trips';
  document.getElementById('fdPts').textContent = pts + ' pts';
  document.getElementById('friendModal').classList.add('open');
}

function closeFriendModal() {
  document.getElementById('friendModal').classList.remove('open');
}

function deleteFriendFromModal() {
  closeFriendModal();
  if (currentViewId) removeCard(currentViewId);
}

function confirmDelete(name, cardId) {
  currentDeleteId = cardId;
  document.getElementById('deleteModalSub').textContent = 'Remove ' + name + ' from your friends list?';
  document.getElementById('deleteModal').classList.add('open');
}

function closeDeleteModal() {
  document.getElementById('deleteModal').classList.remove('open');
}

function doDelete() {
  closeDeleteModal();
  removeCard(currentDeleteId);
}

function removeCard(cardId) {
  var card = document.getElementById(cardId);
  if (card) {
    card.style.transition = 'opacity 0.3s, transform 0.3s';
    card.style.opacity = '0';
    card.style.transform = 'translateX(30px)';
    setTimeout(function() {
      card.remove();
      var remaining = document.querySelectorAll('#tab-my-friends .friend-card').length;
      var countEl = document.getElementById('friendCount');
      if (countEl) countEl.textContent = remaining;
      showToast('✅ Friend removed');
    }, 300);
  }
}

function acceptFriend(name) {
  showToast('✅ ' + name + ' added as friend!');
}

document.addEventListener('DOMContentLoaded', function() {
  ['friendModal','deleteModal'].forEach(function(id) {
    var m = document.getElementById(id);
    if (m) m.addEventListener('click', function(e){ if(e.target===m) m.classList.remove('open'); });
  });
});

initRiderFriends();
