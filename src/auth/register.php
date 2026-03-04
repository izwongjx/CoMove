<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoRide - Sign Up</title>
  <link rel="icon" type="image/svg+xml" href="../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="../public-assets/style.css">
  <link rel="stylesheet" href="auth.css">
</head>

<body>
  <div class="auth-layout">
    <div class="auth-visual">
      <div class="auth-visual-content">
        <h2>JOIN THE<br><span>REVOLUTION</span></h2>
        <p>Every shared ride is a vote for a cleaner planet. Be part of the solution, not the pollution.</p>
      </div>
    </div>

    <div class="auth-form-panel">
      <a href="../../index.php" class="back-link">
        <img src="../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> BACK TO HOME
      </a>

      <div class="auth-form-wrapper">
        <div id="step-role" class="register-step active">
          <div class="auth-header">
            <h1>Join EcoRide</h1>
            <p>Choose how you want to contribute to a greener future</p>
          </div>

          <div class="register-role-options">
            <button class="register-role-option" id="selectRider">
              <div class="register-role-option-icon register-role-option-icon-rider"><img src="../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></div>
              <span class="register-role-option-label">Be a Rider</span>
              <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="register-role-option-arrow icon-img" alt="" aria-hidden="true">
            </button>
            <button class="register-role-option" id="selectDriver">
              <div class="register-role-option-icon register-role-option-icon-driver"><img src="../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></div>
              <span class="register-role-option-label">Be a Driver</span>
              <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="register-role-option-arrow icon-img" alt="" aria-hidden="true">
            </button>
          </div>

          <div class="auth-footer-text">
            <p>Already have an account? <a href="login.php"><strong>Log in</strong></a></p>
          </div>
        </div>

        <div id="step-rider" class="register-step">
          <div class="auth-header">
            <button type="button" class="back-step-link" id="riderBackToRole"><img src="../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Back to role selection</button>
            <h1>Rider Sign Up</h1>
            <p>Create your rider account</p>
          </div>

          <form id="riderForm">
            <div class="form-group"><label class="form-label">Full Name</label>
              <div class="input-wrapper"><img src="../public-assets/icons/user.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="text" class="form-input form-input-icon" name="fullName" placeholder="John Doe" required></div>
            </div>
            <div class="form-group"><label class="form-label">University Email</label>
              <div class="input-wrapper"><img src="../public-assets/icons/mail.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="email" class="form-input form-input-icon" name="email" placeholder="xxx@mail.apu.edu.my" required></div>
            </div>
            <div class="form-group"><label class="form-label">Phone Number</label>
              <div class="input-wrapper"><img src="../public-assets/icons/phone.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="tel" class="form-input form-input-icon" name="phone" placeholder="+1 (555) 000-0000" required></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label class="form-label">Password</label>
                <div class="input-wrapper"><img src="../public-assets/icons/lock.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="password" class="form-input form-input-icon" name="password" placeholder="********" required></div>
              </div>
              <div class="form-group"><label class="form-label">Confirm</label>
                <div class="input-wrapper"><img src="../public-assets/icons/lock.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="password" class="form-input form-input-icon" name="confirmPassword" placeholder="********" required></div>
              </div>
            </div>
            <button type="submit" class="rider-submit-button">CREATE RIDER ACCOUNT <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></button>
          </form>
        </div>

        <div id="step-driver" class="register-step">
          <div class="auth-header">
            <button type="button" class="back-step-link" id="driverBackToRole"><img src="../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Back to role selection</button>
            <h1>Driver Sign Up</h1>
            <p>Complete your driver registration</p>
          </div>

          <div class="driver-step-counter" id="driverStepCounter">Step 1 of 4 - Account Details</div>
          <div class="progress-bar" id="driverProgress">
            <div class="step active"></div>
            <div class="step"></div>
            <div class="step"></div>
            <div class="step"></div>
          </div>

          <form id="driverForm" novalidate>
            <div class="driver-step active" id="driverStep1">
              <h3 class="step-title">Account Details</h3>
              <div class="form-group"><label class="form-label">Full Name</label>
                <div class="input-wrapper"><img src="../public-assets/icons/user.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="text" class="form-input form-input-icon" name="name" maxlength="50" placeholder="John Doe" required></div>
              </div>
              <div class="form-group"><label class="form-label">Email Address</label>
                <div class="input-wrapper"><img src="../public-assets/icons/mail.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="email" class="form-input form-input-icon" name="email" maxlength="100" placeholder="xxx@mail.apu.edu.my" required></div>
              </div>
              <div class="form-group"><label class="form-label">Phone Number</label>
                <div class="input-wrapper"><img src="../public-assets/icons/phone.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="tel" class="form-input form-input-icon" name="phone_number" maxlength="20" placeholder="+1 (555) 000-0000" required></div>
              </div>
              <div class="form-group"><label class="form-label">Password</label>
                <div class="input-wrapper"><img src="../public-assets/icons/lock.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="password" class="form-input form-input-icon" name="password" maxlength="255" placeholder="********" required></div>
              </div>
              <div class="form-group"><label class="form-label">Confirm Password</label>
                <div class="input-wrapper"><img src="../public-assets/icons/lock.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="password" class="form-input form-input-icon" name="confirmPassword" maxlength="255" placeholder="********" required></div>
              </div>
              <div class="form-group"><label class="form-label">Profile Photo (Optional)</label><label class="file-upload"><input type="file" accept="image/*" name="profile_photo"><img src="../public-assets/icons/upload.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"><span class="file-upload-text">Upload Photo</span></label></div>
            </div>

            <div class="driver-step" id="driverStep2">
              <h3 class="step-title">Identity Verification</h3>
              <div class="form-group"><label class="form-label">NRIC Number</label>
                <div class="input-wrapper"><img src="../public-assets/icons/credit-card.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="text" class="form-input form-input-icon" name="nric_number" maxlength="12" placeholder="S1234567A" required></div>
              </div>
              <div class="form-group"><label class="form-label">NRIC Photo (Front)</label><label class="file-upload"><input type="file" accept="image/*" name="nric_front_image" required><img src="../public-assets/icons/upload.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"><span class="file-upload-text">Upload Front</span></label></div>
              <div class="form-group"><label class="form-label">NRIC Photo (Back)</label><label class="file-upload"><input type="file" accept="image/*" name="nric_back_image" required><img src="../public-assets/icons/upload.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"><span class="file-upload-text">Upload Back</span></label></div>
            </div>

            <div class="driver-step" id="driverStep3">
              <h3 class="step-title">License Verification</h3>
              <div class="form-group"><label class="form-label">License Expiry Date</label>
                <div class="input-wrapper"><img src="../public-assets/icons/calendar.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="date" class="form-input form-input-icon" name="lisence_expiry_date" required></div>
              </div>
              <div class="form-group"><label class="form-label">License Photo (Front)</label><label class="file-upload"><input type="file" accept="image/*" name="lisence_front_image" required><img src="../public-assets/icons/upload.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"><span class="file-upload-text">Upload Front</span></label></div>
              <div class="form-group"><label class="form-label">License Photo (Back)</label><label class="file-upload"><input type="file" accept="image/*" name="lisence_back_image" required><img src="../public-assets/icons/upload.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"><span class="file-upload-text">Upload Back</span></label></div>
            </div>

            <div class="driver-step" id="driverStep4">
              <h3 class="step-title">Vehicle Information</h3>
              <div class="form-group"><label class="form-label">Vehicle Model</label>
                <div class="input-wrapper"><img src="../public-assets/icons/car.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="text" class="form-input form-input-icon" name="vehicle_type" maxlength="20" placeholder="Toyota Prius 2022"></div>
              </div>
              <div class="form-group"><label class="form-label">Plate Number</label>
                <div class="input-wrapper"><img src="../public-assets/icons/car.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="text" class="form-input form-input-icon" name="plate_number" maxlength="10" placeholder="ABC1234" required></div>
              </div>
              <div class="form-group"><label class="form-label">Color</label>
                <div class="input-wrapper"><img src="../public-assets/icons/edit.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true"><input type="text" class="form-input form-input-icon" name="color" maxlength="20" placeholder="Silver"></div>
              </div>
            </div>

            <div class="driver-nav-buttons"><button type="button" class="driver-back-button" id="driverBack">BACK</button><button type="submit" class="driver-next-button" id="driverNext">NEXT STEP <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></button></div>
          </form>
        </div>

        <div id="step-otp" class="register-step">
          <div class="auth-header">
            <h1>Verify Your Account</h1>
            <p id="otpSubtitle">We've sent a verification code to your email</p>
          </div>
          <form id="otpForm">
            <div class="otp-inputs"><input type="text" maxlength="1" class="otp-input" data-index="0"><input type="text" maxlength="1" class="otp-input" data-index="1"><input type="text" maxlength="1" class="otp-input" data-index="2"><input type="text" maxlength="1" class="otp-input" data-index="3"><input type="text" maxlength="1" class="otp-input" data-index="4"><input type="text" maxlength="1" class="otp-input" data-index="5"></div>
            <div class="otp-resend">
              <p>Didn't receive the code?</p><button type="button" id="resendBtn" class="resend-link" disabled><img src="../public-assets/icons/refresh.svg" width="14" height="14" class="icon-img" alt="" aria-hidden="true"><span id="resendText">Resend in 30s</span></button>
            </div>
            <button type="submit" class="verify-submit-button" id="verifyBtn" disabled>VERIFY & CONTINUE <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="../public-assets/script.js"></script>
  <script src="register.js"></script>
</body>

</html>
