/* Comove – Profile JS */
function initRiderProfile() {}
function toggleSwitch(el) { el.classList.toggle('on'); }
function openLogoutModal() { document.getElementById('logoutModal').classList.add('open'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('open'); }
document.addEventListener('DOMContentLoaded', function() {
  var m = document.getElementById('logoutModal');
  if (m) m.addEventListener('click', function(e){ if(e.target===m) closeLogoutModal(); });
});
initRiderProfile();
