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
  var otpInputs = document.querySelectorAll('.otp-input');
  var verifyBtn = document.getElementById('verifyBtn');
  var resendBtn = document.getElementById('resendBtn');
  var resendText = document.getElementById('resendText');

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
        otpSection.classList.remove('is-hidden');
        otpSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      for (var i = 0; i < otpInputs.length; i++) {
        otpInputs[i].value = '';
      }
      if (otpInputs.length) otpInputs[0].focus();
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

      if (!/^\d{6}$/.test(otp)) {
        alert('Please enter a valid 6-digit OTP.');
        return;
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
