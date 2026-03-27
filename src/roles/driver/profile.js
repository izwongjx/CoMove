/* Page script: profile (driver) */
function initDriverProfile() {
  const btn = document.getElementById('editProfileBtn');
  const modal = document.getElementById('editProfileModal');
  const closeBtn = modal.querySelector('.close');

  // Open modal when Edit Profile is clicked
  if (btn && modal) {
    btn.addEventListener('click', function () {
      modal.style.display = 'flex';
    });
  }

  // Close modal when the 'x' is clicked
  if (closeBtn && modal) {
    closeBtn.addEventListener('click', function () {
      modal.style.display = 'none';
    });
  }

  // Close modal when clicking outside the box
  window.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
}

initDriverProfile();