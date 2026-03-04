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

    //this part should check the database for the email and password, 
    // but since we don't have a backend, we'll just log it and redirect

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

      //when users click on the rider button, 
      // it will lead to the div that has the 'step-rider' class name
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
    if (counter) counter.textContent = 'Step ' + step + ' of 4 - ' + stepLabels[step - 1];

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
        inputs[i].style.borderColor = '';
      }
    }

    return true;
  }

  function getOptionalFile(formData, fieldName) {
    var fileValue = formData.get(fieldName);
    if (!fileValue || !fileValue.name) return null;
    return fileValue;
  }

  function getDriverPayload(formData) {
    return {
      name: (formData.get('name') || '').trim(),
      email: (formData.get('email') || '').trim(),
      password: (formData.get('password') || '').trim(),
      phone_number: (formData.get('phone_number') || '').trim(),
      profile_photo: getOptionalFile(formData, 'profile_photo'),
      nric_number: (formData.get('nric_number') || '').trim().toUpperCase(),
      nric_front_image: formData.get('nric_front_image'),
      nric_back_image: formData.get('nric_back_image'),
      lisence_front_image: formData.get('lisence_front_image'),
      lisence_back_image: formData.get('lisence_back_image'),
      lisence_expiry_date: formData.get('lisence_expiry_date'),
      vehicle_type: ((formData.get('vehicle_type') || '').trim() || null),
      plate_number: (formData.get('plate_number') || '').trim().toUpperCase(),
      color: ((formData.get('color') || '').trim() || null)
    };
  }

  var driverForm = document.getElementById('driverForm');
  if (driverForm) {
    driverForm.addEventListener('submit', function(e) {
      e.preventDefault();

      if (!validateDriverStep(driverStep)) return;

      if (driverStep === 1) {
        var passwordInput = this.elements.password;
        var confirmPasswordInput = this.elements.confirmPassword;
        var password = passwordInput ? passwordInput.value : '';
        var confirmPassword = confirmPasswordInput ? confirmPasswordInput.value : '';

        if (password !== confirmPassword) {
          if (confirmPasswordInput) {
            confirmPasswordInput.style.borderColor = 'var(--red-500)';
            confirmPasswordInput.focus();
          }
          return;
        }

        if (confirmPasswordInput) confirmPasswordInput.style.borderColor = '';
      }

      if (driverStep < 4) {
        showDriverStep(driverStep + 1);
      } else {
        var formData = new FormData(this);
        var driverPayload = getDriverPayload(formData);
        console.log('Driver Signup Payload:', driverPayload);
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
