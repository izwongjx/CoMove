/* ============================================
   ECORIDE - Role Registration With OTP
   ============================================ */

(function initRegisterRolePage() {
  var riderForm = document.getElementById('riderRegisterForm');
  var driverForm = document.getElementById('driverRegisterForm');
  var activeForm = riderForm || driverForm;
  if (!activeForm) return;

  var submitBtn = document.getElementById('registerSubmitBtn');
  var otpSection = document.getElementById('otpSection');
  var otpSubtitle = document.getElementById('otpSubtitle');
  var otpForm = document.getElementById('otpForm');
  var otpInputs = document.querySelectorAll('.otp-digit');
  var verifyBtn = document.getElementById('verifyBtn');
  var resendBtn = document.getElementById('resendBtn');
  var resendText = document.getElementById('resendText');
  var otpCodeInput = activeForm.elements.otp_code;

  var pendingOtpEmail = '';
  var resendInterval;
  var resendTimer = 30;
  var otpRequested = false;

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

  function requestOtp(email) {
    return fetch('../config/send-otp.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email: email })
    }).then(function(response) {
      return response.json().catch(function() {
        return { success: false, message: 'Invalid server response.' };
      }).then(function(payload) {
        if (!response.ok || !payload.success) {
          throw new Error(payload.message || 'Unable to send OTP at the moment.');
        }
        return payload;
      });
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

  function resetOtpInputs() {
    for (var i = 0; i < otpInputs.length; i++) {
      otpInputs[i].value = '';
    }
    if (otpInputs.length > 0) {
      otpInputs[0].focus();
    }
    if (verifyBtn) verifyBtn.disabled = true;
  }

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

  activeForm.addEventListener('submit', function(e) {
    if (activeForm.dataset.otpVerified === '1') {
      return;
    }

    e.preventDefault();

    var emailInput = activeForm.elements.email;
    var passwordInput = activeForm.elements.password;
    var confirmPasswordInput = activeForm.elements.confirmPassword;

    var email = emailInput ? emailInput.value.trim().toLowerCase() : '';
    var password = passwordInput ? passwordInput.value : '';
    var confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

    if (!isApuEmail(email)) {
      alert('Use APU email format: xxx@mail.apu.edu.my');
      if (emailInput) emailInput.focus();
      return;
    }

    if (password.length < 8) {
      alert('Password must be at least 8 characters.');
      if (passwordInput) passwordInput.focus();
      return;
    }

    if (password !== confirmPassword) {
      alert('Password and confirm password do not match.');
      if (confirmPasswordInput) confirmPasswordInput.focus();
      return;
    }

    if (!activeForm.checkValidity()) {
      activeForm.reportValidity();
      return;
    }

    setButtonLoading(submitBtn, true, 'SENDING OTP...');

    requestOtp(email).then(function() {
      otpRequested = true;
      pendingOtpEmail = email;
      if (otpCodeInput) {
        otpCodeInput.value = '';
      }

      if (otpSubtitle) {
        otpSubtitle.textContent = "We've sent a verification code to " + pendingOtpEmail;
      }

      if (otpSection) {
        activeForm.classList.add('form-hidden');
        otpSection.classList.remove('otp-hidden');
      }

      resetOtpInputs();
      startOtpTimer();
    }).catch(function(err) {
      alert(err && err.message ? err.message : 'Unable to send OTP. Please try again.');
    }).finally(function() {
      setButtonLoading(submitBtn, false);
    });
  });

  for (var i = 0; i < otpInputs.length; i++) {
    (function(index) {
      otpInputs[index].addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 1);

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

      if (!otpRequested) {
        alert('No pending registration found. Please submit the form again.');
        return;
      }

      var otp = '';
      for (var i = 0; i < otpInputs.length; i++) {
        otp += otpInputs[i].value;
      }

      if (otp.length !== 6) {
        alert('Enter the 6-digit OTP code.');
        return;
      }

      if (otpCodeInput) {
        otpCodeInput.value = otp;
      }

      setButtonLoading(verifyBtn, true, 'VERIFYING...');
      activeForm.dataset.otpVerified = '1';
      activeForm.submit();
    });
  }

  if (resendBtn) {
    resendBtn.addEventListener('click', function() {
      if (resendBtn.disabled || !pendingOtpEmail) return;

      resendBtn.disabled = true;
      resendText.textContent = 'Sending...';

      requestOtp(pendingOtpEmail).then(function() {
        alert('A new OTP has been sent to your email.');
        resetOtpInputs();
        startOtpTimer();
      }).catch(function(err) {
        alert(err && err.message ? err.message : 'Unable to resend OTP.');
        resendBtn.disabled = false;
        resendText.textContent = 'Resend Code';
      });
    });
  }
})();
