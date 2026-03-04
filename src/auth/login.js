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
  var loginBtn = document.getElementById('loginBtn');

  function setRole(role) {
    currentRole = role;

    riderBtn.classList.toggle('active', role === 'rider');
    driverBtn.classList.toggle('active', role === 'driver');

    emailLabel.textContent = 'APU Email Address';
    emailInput.placeholder = 'xxx@mail.apu.edu.my';
    roleText.textContent = role.toUpperCase();
  }

  function isApuEmail(email) {
    return /^[A-Za-z0-9._%+-]+@mail\.apu\.edu\.my$/i.test(email);
  }

  async function loginWithServer(role, email, password) {
    var response = await fetch('login-handler.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        role: role,
        email: email,
        password: password
      })
    });

    var result = {};
    try {
      result = await response.json();
    } catch (error) {
      result = {};
    }

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Login failed.');
    }

    return result;
  }

  setRole(currentRole);

  riderBtn.addEventListener('click', function() {
    setRole('rider');
  });

  driverBtn.addEventListener('click', function() {
    setRole('driver');
  });

  loginForm.addEventListener('submit', async function(event) {
    event.preventDefault();

    var email = emailInput.value.trim();
    var password = passwordInput.value;

    if (!isApuEmail(email)) {
      alert('Please use APU email format: xxx@mail.apu.edu.my');
      emailInput.focus();
      return;
    }

    if (!password) {
      alert('Password is required.');
      passwordInput.focus();
      return;
    }

    var originalBtnHtml = loginBtn ? loginBtn.innerHTML : '';
    if (loginBtn) {
      loginBtn.disabled = true;
      loginBtn.textContent = 'SIGNING IN...';
    }

    try {
      var result = await loginWithServer(currentRole, email, password);
      window.location.href = result.redirect_url || (currentRole === 'driver'
        ? '../roles/driver/dashboard.html'
        : '../roles/rider/dashboard.html');
    } catch (error) {
      alert(error.message || 'Unable to log in now.');
    } finally {
      if (loginBtn) {
        loginBtn.disabled = false;
        loginBtn.innerHTML = originalBtnHtml;
      }
    }
  });
})();
