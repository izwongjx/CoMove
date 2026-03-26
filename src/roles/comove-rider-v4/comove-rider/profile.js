/* Comove – Profile JS */
function initRiderProfile() {
  loadProfile();

  var input = document.getElementById('profilePhotoInput');
  if (input) input.addEventListener('change', handleProfilePhotoUpload);
}

function toggleSwitch(el) { el.classList.toggle('on'); }
function openLogoutModal() { document.getElementById('logoutModal').classList.add('open'); }
function closeLogoutModal() { document.getElementById('logoutModal').classList.remove('open'); }

function triggerProfileUpload() {
  var input = document.getElementById('profilePhotoInput');
  if (input) input.click();
}

async function loadProfile() {
  try {
    var profile = await apiGet('api/profile.php');
    renderProfile(profile);
  } catch (err) {
    showToast('⚠️ Unable to load profile');
  }
}

function renderProfile(profile) {
  var name = profile.name || 'Rider';
  var email = profile.email || '';
  var photoUrl = profile.photo_url || 'assets/avatars/default-profile.svg';

  document.getElementById('profileAvatarImg').src = photoUrl;
  document.getElementById('profileName').textContent = name;
  document.getElementById('profileFullName').textContent = profile.full_name || name;
  document.getElementById('profileEmailHero').textContent = email;
  document.getElementById('profileEmail').textContent = email;
  document.getElementById('profilePhone').textContent = profile.phone_number || '-';
  document.getElementById('profilePoints').textContent = profile.green_points || 0;
  document.getElementById('profileTrips').textContent = profile.total_trips || 0;
  document.getElementById('profileRole').textContent = '🚗 Rider';
}

async function handleProfilePhotoUpload(e) {
  var file = e.target.files && e.target.files[0];
  if (!file) return;

  var formData = new FormData();
  formData.append('photo', file);

  try {
    var data = await apiPost('api/profile.php', formData);
    document.getElementById('profileAvatarImg').src = data.photo_url;
    showToast('✅ Profile photo updated');
  } catch (err) {
    showToast('⚠️ ' + err.message);
  }

  e.target.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
  var m = document.getElementById('logoutModal');
  if (m) m.addEventListener('click', function(e){ if(e.target===m) closeLogoutModal(); });
  initRiderProfile();
});
