// ... existing code from auth/auth.js but with updated paths ...
/* ============================================
   ECORIDE - Auth Pages Logic
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {
  var loginForm = document.getElementById('loginForm');
  var stepRole = document.getElementById('step-role');
  if (loginForm) initLoginPage();
  if (stepRole) initRegisterPage();
});

function initLoginPage() {
  var currentRole = 'rider';
  var riderBtn = document.getElementById('riderToggle');
  var driverBtn = document.getElementById('driverToggle');
  var emailLabel = document.getElementById('emailLabel');
  var emailInput = document.getElementById('loginEmail');
  var roleText = document.getElementById('roleText');
  var loginForm = document.getElementById('loginForm');

  function setRole(role) {
    currentRole = role;
    riderBtn.classList.toggle('active', role === 'rider');
    driverBtn.classList.toggle('active', role === 'driver');
    riderBtn.dataset.role = 'rider';
    driverBtn.dataset.role = 'driver';
    if (role === 'rider') {emailLabel.textContent = 'Student/Lecturer Email';emailInput.placeholder = 'your.name@university.edu';} else
    {emailLabel.textContent = 'Email Address';emailInput.placeholder = 'name@example.com';}
    roleText.textContent = role.toUpperCase();
  }

  riderBtn.addEventListener('click', function () {setRole('rider');});
  driverBtn.addEventListener('click', function () {setRole('driver');});

  loginForm.addEventListener('submit', function (e) {
    e.preventDefault();
    var email = emailInput.value;
    var password = document.getElementById('loginPassword').value;
    console.log('Login:', { role: currentRole, email: email, password: password });
    if (currentRole === 'driver') {
      window.location.href = '../roles/driver/dashboard.html';
    } else {
      window.location.href = '../roles/rider/dashboard.html';
    }
  });
}

function initRegisterPage() {
  var selectedRole = '';
  var driverStep = 1;
  var resendTimer = 30;
  var resendInterval = null;

  function showStep(stepId) {
    document.querySelectorAll('.register-step').forEach(function (s) {s.classList.remove('active');});
    var el = document.getElementById(stepId);
    if (el) el.classList.add('active');
  }

  document.getElementById('selectRider').addEventListener('click', function () {selectedRole = 'rider';showStep('step-rider');});
  document.getElementById('selectDriver').addEventListener('click', function () {selectedRole = 'driver';driverStep = 1;showDriverStep(1);showStep('step-driver');});

  var riderBackBtn = document.getElementById('riderBackToRole');
  if (riderBackBtn) {riderBackBtn.addEventListener('click', function () {showStep('step-role');});}
  var driverBackToRoleBtn = document.getElementById('driverBackToRole');
  if (driverBackToRoleBtn) {driverBackToRoleBtn.addEventListener('click', function () {showStep('step-role');});}

  document.getElementById('riderForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    console.log('Rider Signup:', Object.fromEntries(formData));
    showStep('step-otp');
    startOtpTimer();
  });

  var driverSteps = document.querySelectorAll('.driver-step');
  var progressSteps = document.querySelectorAll('#driverProgress .step');
  var backBtn = document.getElementById('driverBack');
  var nextBtn = document.getElementById('driverNext');
  var stepLabels = ['Account Details', 'Identity Verification', "Driver's License", 'Vehicle Details'];

  function showDriverStep(step) {
    driverStep = step;
    driverSteps.forEach(function (s, i) {s.classList.toggle('active', i === step - 1);});
    progressSteps.forEach(function (s, i) {s.classList.toggle('active', i < step);});
    var stepCounter = document.getElementById('driverStepCounter');
    if (stepCounter) stepCounter.textContent = 'Step ' + step + ' of 4 ?' + stepLabels[step - 1];
    if (backBtn) backBtn.style.display = step > 1 ? '' : 'none';
    if (driverBackToRoleBtn) driverBackToRoleBtn.style.display = step === 1 ? '' : 'none';
    if (step === 4) {nextBtn.innerHTML = 'COMPLETE REGISTRATION <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true">';} else
    {nextBtn.innerHTML = 'NEXT STEP <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true">';}
  }

  backBtn.addEventListener('click', function () {if (driverStep > 1) showDriverStep(driverStep - 1);});

  function validateDriverStep(step) {
    var stepEl = document.getElementById('driverStep' + step);if (!stepEl) return true;
    var inputs = stepEl.querySelectorAll('input[required], select[required], textarea[required]');
    var valid = true;
    inputs.forEach(function (input) {input.style.borderColor = '';if (!input.value.trim()) {valid = false;input.style.borderColor = 'var(--red-500)';input.focus();}});
    return valid;
  }

  document.getElementById('driverForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!validateDriverStep(driverStep)) return;
    if (driverStep < 4) {showDriverStep(driverStep + 1);} else
    {
      var formData = new FormData(this);
      console.log('Driver Signup Complete:', Object.fromEntries(formData));
      nextBtn.innerHTML = '<img src="../public-assets/icons/refresh.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"> SUBMITTING...';
      nextBtn.disabled = true;
      setTimeout(function () {nextBtn.disabled = false;showStep('step-otp');startOtpTimer();}, 800);
    }
  });

  document.querySelectorAll('.file-upload input[type="file"]').forEach(function (input) {
    input.addEventListener('change', function () {
      var label = this.closest('.file-upload');
      if (this.files && this.files[0]) {label.classList.add('uploaded');var textEl = label.querySelector('.file-upload-text');if (textEl) textEl.textContent = this.files[0].name;}
    });
  });

  var otpInputs = document.querySelectorAll('.otp-input');
  var verifyBtn = document.getElementById('verifyBtn');

  otpInputs.forEach(function (input, index) {
    input.addEventListener('input', function () {if (isNaN(this.value)) {this.value = '';return;}if (this.value && index < otpInputs.length - 1) otpInputs[index + 1].focus();checkOtpComplete();});
    input.addEventListener('keydown', function (e) {if (e.key === 'Backspace' && !this.value && index > 0) otpInputs[index - 1].focus();});
    input.addEventListener('focus', function () {this.select();});
  });

  function checkOtpComplete() {var allFilled = true;otpInputs.forEach(function (inp) {if (!inp.value) allFilled = false;});verifyBtn.disabled = !allFilled;}

  document.getElementById('otpForm').addEventListener('submit', function (e) {
    e.preventDefault();
    var otp = '';otpInputs.forEach(function (inp) {otp += inp.value;});
    console.log('OTP Verified:', otp);
    verifyBtn.innerHTML = '<img src="../public-assets/icons/refresh.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"> VERIFYING...';
    verifyBtn.disabled = true;
    setTimeout(function () {
      if (selectedRole === 'driver') {window.location.href = '../roles/driver/dashboard.html';} else
      {window.location.href = '../roles/rider/dashboard.html';}
    }, 1500);
  });

  function startOtpTimer() {
    resendTimer = 30;
    var resendBtn = document.getElementById('resendBtn');
    var resendText = document.getElementById('resendText');
    resendBtn.disabled = true;
    if (resendInterval) clearInterval(resendInterval);
    resendInterval = setInterval(function () {
      resendTimer--;
      if (resendTimer <= 0) {clearInterval(resendInterval);resendText.textContent = 'Resend Code';resendBtn.disabled = false;} else
      {resendText.textContent = 'Resend in ' + resendTimer + 's';}
    }, 1000);
    resendBtn.addEventListener('click', function () {if (!this.disabled) {console.log('Resending OTP...');startOtpTimer();}});
  }
}

