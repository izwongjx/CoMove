/* ============================================
   ECORIDE - Login Page Logic
   ============================================ */

(function initLoginPage() {
  var loginForm = document.getElementById('loginForm');
  if (!loginForm) return;

  var currentRole = 'rider';
  var riderBtn = document.getElementById('riderToggle');
  var driverBtn = document.getElementById('driverToggle');
  var emailLabel = document.getElementById('emailLabel');
  var emailInput = document.getElementById('loginEmail');
  var roleText = document.getElementById('roleText');
  var passwordInput = document.getElementById('loginPassword');

  function setRole(role) {
    currentRole = role;

    riderBtn.classList.toggle('active', role === 'rider');
    driverBtn.classList.toggle('active', role === 'driver');

    emailLabel.textContent = 'APU Email Address';
    emailInput.placeholder = 'example@apu.edu.my';
    roleText.textContent = role.toUpperCase();
  }

  setRole(currentRole);

  riderBtn.addEventListener('click', function() {
    setRole('rider');
  });

  driverBtn.addEventListener('click', function() {
    setRole('driver');
  });

  loginForm.addEventListener('submit', function(event) {
    event.preventDefault();

    var email = emailInput.value.trim();
    var password = passwordInput.value.trim();

    // TODO: replace with backend authentication.
    console.log('Login:', { role: currentRole, email: email, password: password });

    if (currentRole === 'driver') {
      window.location.href = '../roles/driver/dashboard.html';
    } else {
      window.location.href = '../roles/rider/dashboard.html';
    }
  });
})();
