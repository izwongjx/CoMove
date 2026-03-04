/* ============================================
   ECORIDE - Register Page Logic
   ============================================ */

(function initRegisterPage() {
  var registerRoleStep = document.getElementById('step-role');
  if (!registerRoleStep) return;

  var selectedRole = '';
  var driverStep = 1;
  var resendTimer = 30;
  var resendInterval;
  var pendingOtpEmail = '';
  var otpSubtitle = document.getElementById('otpSubtitle');

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

  async function parseApiResponse(response) {
    var result = {};
    try {
      result = await response.json();
    } catch (error) {
      result = {};
    }

    if (!response.ok || !result.success) {
      throw new Error(result.message || 'Request failed.');
    }

    return result;
  }

  async function sendOtpToEmail(email) {
    var normalizedEmail = (email || '').trim().toLowerCase();
    if (!normalizedEmail) {
      return { success: false, message: 'Email is required.' };
    }

    try {
      var response = await fetch('../config/send-otp.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: normalizedEmail, role: selectedRole })
      });

      var result = await parseApiResponse(response);
      pendingOtpEmail = normalizedEmail;
      if (otpSubtitle) {
        otpSubtitle.textContent = "We've sent a verification code to " + normalizedEmail;
      }

      return {
        success: true,
        message: result.message || 'OTP sent.'
      };
    } catch (error) {
      return {
        success: false,
        message: error.message || 'Unable to resend OTP now.'
      };
    }
  }

  async function startRegistration(formEl, role) {
    var formData = new FormData(formEl);
    var email = (formData.get('email') || '').trim().toLowerCase();
    if (!isApuEmail(email)) {
      throw new Error('Use APU email format: xxx@mail.apu.edu.my');
    }

    formData.set('email', email);
    formData.set('role', role);

    var response = await fetch('register-init.php', {
      method: 'POST',
      body: formData
    });

    var result = await parseApiResponse(response);
    pendingOtpEmail = result.email || email;
    selectedRole = role;

    if (otpSubtitle) {
      otpSubtitle.textContent = "We've sent a verification code to " + pendingOtpEmail;
    }
  }

  async function verifyRegistrationOtp(role, email, otp) {
    var response = await fetch('register-verify.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        role: role,
        email: email,
        otp: otp
      })
    });

    return parseApiResponse(response);
  }

  function showStep(stepId) {
    var steps = document.querySelectorAll('.register-step');
    for (var i = 0; i < steps.length; i++) {
      steps[i].classList.remove('active');
    }

    var activeStep = document.getElementById(stepId);
    if (activeStep) activeStep.classList.add('active');
  }

  function setupFileUploadFeedback() {
    var fileInputs = document.querySelectorAll('.file-upload input[type="file"]');

    function clearPreview(uploadLabel) {
      var previewEl = uploadLabel.parentElement.querySelector('.file-preview');
      if (!previewEl) return;

      var previewImg = previewEl.querySelector('.file-preview-image');
      if (previewImg && previewImg.dataset.objectUrl) {
        URL.revokeObjectURL(previewImg.dataset.objectUrl);
      }

      previewEl.remove();
    }

    for (var i = 0; i < fileInputs.length; i++) {
      (function(fileInput) {
        var uploadLabel = fileInput.closest('.file-upload');
        if (!uploadLabel) return;

        var textEl = uploadLabel.querySelector('.file-upload-text');
        if (textEl && !textEl.dataset.defaultText) {
          textEl.dataset.defaultText = textEl.textContent;
        }

        fileInput.addEventListener('change', function() {
          clearPreview(uploadLabel);

          var selectedFile = this.files && this.files.length ? this.files[0] : null;
          if (!selectedFile) {
            uploadLabel.classList.remove('has-file');
            if (textEl) textEl.textContent = textEl.dataset.defaultText || 'Upload File';
            return;
          }

          uploadLabel.classList.add('has-file');
          if (textEl) textEl.textContent = selectedFile.name;

          var previewEl = document.createElement('div');
          previewEl.className = 'file-preview';

          if (selectedFile.type.indexOf('image/') === 0) {
            var previewImage = document.createElement('img');
            var objectUrl = URL.createObjectURL(selectedFile);
            previewImage.src = objectUrl;
            previewImage.dataset.objectUrl = objectUrl;
            previewImage.className = 'file-preview-image';
            previewImage.alt = 'Selected file preview';
            previewEl.appendChild(previewImage);
          } else {
            var previewName = document.createElement('p');
            previewName.className = 'file-preview-name';
            previewName.textContent = selectedFile.name;
            previewEl.appendChild(previewName);
          }

          uploadLabel.insertAdjacentElement('afterend', previewEl);
        });
      })(fileInputs[i]);
    }
  }

  setupFileUploadFeedback();

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
    riderForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      var submitBtn = this.querySelector('button[type="submit"]');
      var email = (this.elements.email && this.elements.email.value || '').trim();
      var password = this.elements.password ? this.elements.password.value : '';
      var confirmPassword = this.elements.confirmPassword ? this.elements.confirmPassword.value : '';

      if (!isApuEmail(email)) {
        alert('Use APU email format: xxx@mail.apu.edu.my');
        this.elements.email.focus();
        return;
      }

      if (password.length < 8) {
        alert('Password must be at least 8 characters.');
        this.elements.password.focus();
        return;
      }

      if (password !== confirmPassword) {
        alert('Password and confirm password do not match.');
        this.elements.confirmPassword.focus();
        return;
      }

      try {
        setButtonLoading(submitBtn, true, 'SENDING OTP...');
        await startRegistration(this, 'rider');
        showStep('step-otp');
        startOtpTimer();
      } catch (error) {
        alert(error.message || 'Unable to start registration.');
      } finally {
        setButtonLoading(submitBtn, false);
      }
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
    'License Verification',
    'Vehicle Information'
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
    if (counter) {
      counter.textContent = 'Step ' + step + ' of 4 - ' + stepLabels[step - 1];
    }

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
      var input = inputs[i];
      var value = input.type === 'file' ? input.value : input.value.trim();

      if (!value) {
        if (input.type === 'file' && input.parentElement) {
          input.parentElement.style.borderColor = 'var(--red-500)';
        }
        input.style.borderColor = 'var(--red-500)';
        input.focus();
        return false;
      }

      if (input.type === 'file' && input.parentElement) {
        input.parentElement.style.borderColor = '';
      } else {
        input.style.borderColor = '';
      }
    }

    return true;
  }

  var driverForm = document.getElementById('driverForm');
  if (driverForm) {
    driverForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      if (!validateDriverStep(driverStep)) return;

      if (driverStep === 1) {
        var driverEmail = this.elements.email ? this.elements.email.value.trim() : '';
        var passwordInput = this.elements.password;
        var confirmPasswordInput = this.elements.confirmPassword;
        var password = passwordInput ? passwordInput.value : '';
        var confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

        if (!isApuEmail(driverEmail)) {
          alert('Use APU email format: xxx@mail.apu.edu.my');
          if (this.elements.email) this.elements.email.focus();
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
      }

      if (driverStep < 4) {
        showDriverStep(driverStep + 1);
        return;
      }

      try {
        setButtonLoading(nextBtn, true, 'SENDING OTP...');
        await startRegistration(this, 'driver');
        showStep('step-otp');
        startOtpTimer();
      } catch (error) {
        alert(error.message || 'Unable to start registration.');
      } finally {
        setButtonLoading(nextBtn, false);
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
    otpForm.addEventListener('submit', async function(e) {
      e.preventDefault();

      if (!pendingOtpEmail || !selectedRole) {
        alert('No pending registration found. Please register again.');
        return;
      }

      var otp = '';
      for (var i = 0; i < otpInputs.length; i++) {
        otp += otpInputs[i].value;
      }

      if (!/^\d{6}$/.test(otp)) {
        alert('Please enter a valid 6-digit OTP.');
        return;
      }

      try {
        setButtonLoading(verifyBtn, true, 'VERIFYING...');
        var result = await verifyRegistrationOtp(selectedRole, pendingOtpEmail, otp);
        alert((result.message || 'Registration completed.') + ' ID: ' + (result.user_id || ''));
        window.location.href = result.redirect_url || (selectedRole === 'driver'
          ? '../roles/driver/dashboard.html'
          : '../roles/rider/dashboard.html');
      } catch (error) {
        alert(error.message || 'OTP verification failed.');
      } finally {
        setButtonLoading(verifyBtn, false);
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
      if (resendBtn.disabled) return;
      if (!pendingOtpEmail) {
        alert('No email found for OTP resend.');
        return;
      }

      resendBtn.disabled = true;
      resendText.textContent = 'Sending...';

      sendOtpToEmail(pendingOtpEmail).then(function(otpResult) {
        if (!otpResult.success) {
          alert(otpResult.message);
          resendText.textContent = 'Resend Code';
          resendBtn.disabled = false;
          return;
        }

        startOtpTimer();
      });
    };
  }
})();