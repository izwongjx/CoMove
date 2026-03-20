
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoRide - Rider Sign Up</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="register.css">
</head>

<body>
  <main class="page">
    <aside class="hero" aria-label="EcoRide Message">
      <div class="hero-content">
        <h2>JOIN THE<br><span>REVOLUTION</span></h2>
        <p>Every shared ride is a vote for a cleaner planet. Be part of the solution, not the pollution.</p>
      </div>
    </aside>

    <section class="panel">
      <nav class="top-nav" aria-label="Page Navigation">
        <a href="register.php" class="back-link">
          <img src="../../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> BACK TO ROLE SELECTION
        </a>
      </nav>

      <div class="form-wrapper">
        <form id="riderRegisterForm" class="account-form" method="post" action="register-handler.php" enctype="multipart/form-data">
          <input type="hidden" name="role" value="rider">
          <input type="hidden" name="otp_code" id="otpCode" value="">
          <header class="section-header">
            <h1>Rider Sign Up</h1>
            <p>Fill once and submit. No multi-step navigation.</p>
          </header>

          <section class="info-section">
            <h3 class="section-title">Account Details</h3>
            <table class="form-table" role="presentation">
              <tr>
                <th class="field-label-cell" scope="row">
                  <label class="form-label" for="riderFullName">Full Name</label>
                </th>
                <td class="field-input-cell">
                  <div class="input-wrap">
                    <input type="text" class="input-control" id="riderFullName" name="fullName" placeholder="John Doe" required>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="field-label-cell" scope="row">
                  <label class="form-label" for="riderEmail">University Email</label>
                </th>
                <td class="field-input-cell">
                  <div class="input-wrap">
                    <input type="email" class="input-control" id="riderEmail" name="email" placeholder="xxx@mail.apu.edu.my" required>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="field-label-cell" scope="row">
                  <label class="form-label" for="riderPhone">Phone Number</label>
                </th>
                <td class="field-input-cell">
                  <div class="input-wrap">
                    <input type="tel" class="input-control" id="riderPhone" name="phone" placeholder="+1 (555) 000-0000" required>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="field-label-cell" scope="row">
                  <label class="form-label" for="riderPassword">Password</label>
                </th>
                <td class="field-input-cell">
                  <div class="input-wrap">
                    <input type="password" class="input-control" id="riderPassword" name="password" placeholder="********" required>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="field-label-cell" scope="row">
                  <label class="form-label" for="riderConfirmPassword">Confirm Password</label>
                </th>
                <td class="field-input-cell">
                  <div class="input-wrap">
                    <input type="password" class="input-control" id="riderConfirmPassword" name="confirmPassword" placeholder="********" required>
                  </div>
                </td>
              </tr>
              <tr>
                <th class="field-label-cell" scope="row">
                  <label class="form-label">Profile Photo (Optional)</label>
                </th>
                <td class="field-input-cell">
                  <input type="file" class="file-input-control" accept="image/*" name="profile_photo">
                </td>
              </tr>
            </table>

            <button type="submit" class="submit-button" id="registerSubmitBtn">
              CREATE RIDER ACCOUNT
            </button>
          </section>
        </form>

        <section class="otp-card otp-hidden" id="otpSection">
          <header class="section-header otp-header">
            <h1>Verify Your Account</h1>
            <p id="otpSubtitle">We have sent a verification code to your email.</p>
          </header>
          <form id="otpForm">
            <div class="otp-grid">
              <input type="text" maxlength="1" class="otp-digit" data-index="0">
              <input type="text" maxlength="1" class="otp-digit" data-index="1">
              <input type="text" maxlength="1" class="otp-digit" data-index="2">
              <input type="text" maxlength="1" class="otp-digit" data-index="3">
              <input type="text" maxlength="1" class="otp-digit" data-index="4">
              <input type="text" maxlength="1" class="otp-digit" data-index="5">
            </div>
            <div class="otp-resend">
              <p>Did not receive the code?</p>
              <button type="button" id="resendBtn" class="resend-btn" disabled>
                <span id="resendText">Resend in 30s</span>
              </button>
            </div>
            <button type="submit" class="submit-button" id="verifyBtn" disabled>
              VERIFY & CONTINUE
            </button>
          </form>
        </section>

        <footer class="section-footer">
          <p>Already have an account? <a href="../login/login-as-rider.php"><strong>Log in</strong></a></p>
        </footer>
      </div>
    </section>
  </main>

  <script src="../../public-assets/script.js"></script>
  <script src="register.js"></script>
</body>

</html>


