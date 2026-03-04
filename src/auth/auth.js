/* ============================================
   ECORIDE - Auth Pages Logic (Clean Version)
   ============================================ */

var loginForm = document.getElementById('loginForm');
var registerRoleStep = document.getElementById('step-role');

if (loginForm) initLoginPage();
if (registerRoleStep) initRegisterPage();
 
/* ============================================
   LOGIN PAGE
============================================ */
function initLoginPage() {
  var currentRole = 'rider';

  var riderBtn = document.getElementById('riderToggle');
  var driverBtn = document.getElementById('driverToggle');
  var emailLabel = document.getElementById('emailLabel');
  var emailInput = document.getElementById('loginEmail');
  var roleText = document.getElementById('roleText');
  var passwordInput = document.getElementById('loginPassword');

  function setRole(role) {

    riderBtn.classList.toggle('active', role === 'rider');
    driverBtn.classList.toggle('active', role === 'driver');

    emailLabel.textContent = 'APU Email Address';
    emailInput.placeholder = 'example@apu.edu.my';

    roleText.textContent = role.toUpperCase();
  }

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

    console.log('Login:', { role: currentRole, email: email, password: password });

    if (currentRole === 'driver') {
      window.location.href = '../roles/driver/dashboard.html';
    } else {
      window.location.href = '../roles/rider/dashboard.html';
    }
  });
}

/* ============================================
   REGISTER PAGE
============================================ */
function initRegisterPage() {
  var selectedRole = '';
  var driverStep = 1;
  var resendTimer = 30;
  var resendInterval;

  function showStep(stepId) {
    var steps = document.querySelectorAll('.register-step');
    for (var i = 0; i < steps.length; i++) {
      steps[i].classList.remove('active');
    }
    var el = document.getElementById(stepId);
    if (el) el.classList.add('active');
  }

  /* -------- Role Selection ------ */
  var selectRider = document.getElementById('selectRider');
  if (selectRider) {
    selectRider.addEventListener('click', function() {
      selectedRole = 'rider';
      showStep('step-rider');
    });
  }

  var selectDriver = document.getElementById('selectDriver');
  if (selectDriver) {
    selectDriver.addEventListener('click', function() {
      selectedRole = 'driver';
      driverStep = 1;
      showDriverStep(1);
      showStep('step-driver');
    });
  }

  var riderBackBtn = document.getElementById('riderBackToRole');
  if (riderBackBtn) {
    riderBackBtn.addEventListener('click', function() {
      showStep('step-role');
    });
  }

  var driverBackToRoleBtn = document.getElementById('driverBackToRole');
  if (driverBackToRoleBtn) {
    driverBackToRoleBtn.addEventListener('click', function() {
      showStep('step-role');
    });
  }

  /* -------- Rider Form -------- */
  var riderForm = document.getElementById('riderForm');
  if (riderForm) {
    riderForm.addEventListener('submit', function(e) {
      e.preventDefault();
      console.log('Rider Signup:', Object.fromEntries(new FormData(this)));
      showStep('step-otp');
      startOtpTimer();
    });
  }

  /* -------- Driver Multi-Step -------- */
  var driverSteps = document.querySelectorAll('.driver-step');
  var progressSteps = document.querySelectorAll('#driverProgress .step');
  var backBtn = document.getElementById('driverBack');
  var nextBtn = document.getElementById('driverNext');

  var stepLabels = [
    'Account Details',
    'Identity Verification',
    "Driver's License",
    'Vehicle Details'
  ];

  function showDriverStep(step) {
    driverStep = step;

    for (var i = 0; i < driverSteps.length; i++) {
      driverSteps[i].classList.toggle('active', i === step - 1);
    }

    for (var j = 0; j < progressSteps.length; j++) {
      progressSteps[j].classList.toggle('active', j < step);
    }

    var counter = document.getElementById('driverStepCounter');
    if (counter) counter.textContent = 'Step ' + step + ' of 4 — ' + stepLabels[step - 1];

    if (backBtn) backBtn.style.display = step > 1 ? '' : 'none';

    if (nextBtn) {
      nextBtn.innerHTML = step === 4 ? 'COMPLETE REGISTRATION' : 'NEXT STEP';
    }
  }

  function validateDriverStep(step) {
    var stepEl = document.getElementById('driverStep' + step);
    if (!stepEl) return true;

    var inputs = stepEl.querySelectorAll('input[required], select[required], textarea[required]');

    for (var i = 0; i < inputs.length; i++) {
      if (!inputs[i].value.trim()) {
        inputs[i].focus();
        inputs[i].style.borderColor = 'var(--red-500)';
        return false;
      }
    }

    return true;
  }

  var driverForm = document.getElementById('driverForm');
  if (driverForm) {
    driverForm.addEventListener('submit', function(e) {
      e.preventDefault();

      if (!validateDriverStep(driverStep)) return;

      if (driverStep < 4) {
        showDriverStep(driverStep + 1);
      } else {
        console.log('Driver Signup:', Object.fromEntries(new FormData(this)));
        showStep('step-otp');
        startOtpTimer();
      }
    });
  }

  if (backBtn) {
    backBtn.addEventListener('click', function() {
      if (driverStep > 1) showDriverStep(driverStep - 1);
    });
  }

  /* -------- OTP Logic -------- */
  var otpInputs = document.querySelectorAll('.otp-input');
  var verifyBtn = document.getElementById('verifyBtn');

  function checkOtpComplete() {
    var complete = true;
    for (var i = 0; i < otpInputs.length; i++) {
      if (!otpInputs[i].value) complete = false;
    }
    if (verifyBtn) verifyBtn.disabled = !complete;
  }

  for (var i = 0; i < otpInputs.length; i++) {
    (function(index) {
      otpInputs[index].addEventListener('input', function() {
        if (isNaN(this.value)) {
          this.value = '';
          return;
        }

        if (this.value && index < otpInputs.length - 1) {
          otpInputs[index + 1].focus();
        }

        checkOtpComplete();
      });

      otpInputs[index].addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && index > 0) {
          otpInputs[index - 1].focus();
        }
      });
    })(i);
  }

  var otpForm = document.getElementById('otpForm');
  if (otpForm) {
    otpForm.addEventListener('submit', function(e) {
      e.preventDefault();

      var otp = '';
      for (var i = 0; i < otpInputs.length; i++) {
        otp += otpInputs[i].value;
      }

      console.log('OTP Verified:', otp);

      if (selectedRole === 'driver') {
        window.location.href = '../roles/driver/dashboard.html';
      } else {
        window.location.href = '../roles/rider/dashboard.html';
      }
    });
  }

  /* -------- OTP Timer -------- */
  function startOtpTimer() {
    resendTimer = 30;

    var resendBtn = document.getElementById('resendBtn');
    var resendText = document.getElementById('resendText');

    if (!resendBtn || !resendText) return;

    resendBtn.disabled = true;
    clearInterval(resendInterval);

    resendInterval = setInterval(function() {
      resendTimer--;

      if (resendTimer <= 0) {
        clearInterval(resendInterval);
        resendText.textContent = 'Resend Code';
        resendBtn.disabled = false;
      } else {
        resendText.textContent = 'Resend in ' + resendTimer + 's';
      }
    }, 1000);

    resendBtn.onclick = function() {
      if (!resendBtn.disabled) {
        console.log('Resending OTP...');
        startOtpTimer();
      }
    };
  }
}