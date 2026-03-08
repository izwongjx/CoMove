/* ============================================
   ECORIDE - Single-Page Role Registration
   ============================================ */

(function initRegisterRolePage() {
  var riderForm = document.getElementById('riderRegisterForm');
  var driverForm = document.getElementById('driverRegisterForm');
  var activeForm = riderForm || driverForm;
  if (!activeForm) return;

  var selectedRole = riderForm ? 'rider' : 'driver';
  var submitBtn = document.getElementById('registerSubmitBtn');
  var otpSection = document.getElementById('otpSection');
  var otpSubtitle = document.getElementById('otpSubtitle');
  var otpForm = document.getElementById('otpForm');
  var otpInputs = document.querySelectorAll('.otp-digit');
  var verifyBtn = document.getElementById('verifyBtn');
  var resendBtn = document.getElementById('resendBtn');
  var resendText = document.getElementById('resendText');
  var formFooter = document.querySelector('.form-wrapper > .section-footer');

  var pendingOtpEmail = '';
  var generatedOtp = '';
  var resendInterval;
  var resendTimer = 30;

  function isApuEmail(email) {
    return /^[A-Za-z0-9._%+-]+@mail\.apu\.edu\.my$/i.test(email || '');
  }

  function setButtonLoading(buttonEl, isLoading, loadingText) {
    if (!buttonEl) return;

    if (isLoading) {
      if (!buttonEl.dataset.originalHtml) {
        buttonEl.dataset.originalHtml = buttonEl.innerHTML;
      }
      buttonEl.disabled = true;
      buttonEl.textContent = loadingText;
      return;
    }

    buttonEl.disabled = false;
    if (buttonEl.dataset.originalHtml) {
      buttonEl.innerHTML = buttonEl.dataset.originalHtml;
    }
  }

  function generateOtpCode() {
    return String(Math.floor(100000 + Math.random() * 900000));
  }

  activeForm.addEventListener('submit', function(e) {
    e.preventDefault();

    var emailInput = this.elements.email;
    var passwordInput = this.elements.password;
    var confirmPasswordInput = this.elements.confirmPassword;

    var email = emailInput ? emailInput.value.trim().toLowerCase() : '';
    var password = passwordInput ? passwordInput.value : '';
    var confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

    if (!isApuEmail(email)) {
      alert('Use APU email format: xxx@mail.apu.edu.my');
      emailInput.focus();
      return;
    }

    if (password.length < 8) {
      alert('Password must be at least 8 characters.');
      passwordInput.focus();
      return;
    }

    if (password !== confirmPassword) {
      alert('Password and confirm password do not match.');
      confirmPasswordInput.focus();
      return;
    }

    if (!this.checkValidity()) {
      this.reportValidity();
      return;
    }

    try {
      setButtonLoading(submitBtn, true, 'SENDING OTP...');
      pendingOtpEmail = email;
      generatedOtp = generateOtpCode();

      if (otpSubtitle) {
        otpSubtitle.textContent = "We've sent a verification code to " + pendingOtpEmail;
      }

      // Frontend-only temporary flow. Remove after backend is wired in.
      alert('Demo OTP: ' + generatedOtp);

      if (otpSection) {
        activeForm.classList.add('form-hidden');
        otpSection.classList.remove('otp-hidden');
      }

      for (var i = 0; i < otpInputs.length; i++) {
        otpInputs[i].value = '';
      }
      otpInputs[0].focus();
      if (verifyBtn) verifyBtn.disabled = true;

      startOtpTimer();
    } finally {
      setButtonLoading(submitBtn, false);
    }
  });

  function checkOtpComplete() {
    var isComplete = true;

    for (var i = 0; i < otpInputs.length; i++) {
      if (!otpInputs[i].value) {
        isComplete = false;
        break;
      }
    }

    if (verifyBtn) verifyBtn.disabled = !isComplete;
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

  if (otpForm) {
    otpForm.addEventListener('submit', function(e) {
      e.preventDefault();

      if (!pendingOtpEmail) {
        alert('No pending registration found. Please submit the form again.');
        return;
      }

      var otp = '';
      for (var i = 0; i < otpInputs.length; i++) {
        otp += otpInputs[i].value;
      }

      if (otp !== generatedOtp) {
        alert('Incorrect OTP code.');
        return;
      }

      setButtonLoading(verifyBtn, true, 'VERIFYING...');
      alert('Registration completed successfully.');
      window.location.href = selectedRole === 'driver'
        ? '../roles/driver/dashboard.html'
        : '../roles/rider/dashboard.html';
    });
  }

  function startOtpTimer() {
    if (!resendBtn || !resendText) return;

    resendTimer = 30;
    resendBtn.disabled = true;
    resendText.textContent = 'Resend in 30s';
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
  }

  if (resendBtn) {
    resendBtn.addEventListener('click', function() {
      if (resendBtn.disabled) return;

      resendBtn.disabled = true;
      resendText.textContent = 'Sending...';

      generatedOtp = generateOtpCode();
      alert('New demo OTP: ' + generatedOtp);
      startOtpTimer();
    });
  }
})();
