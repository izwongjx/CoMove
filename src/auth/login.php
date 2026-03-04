<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoRide - Log In</title>
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
        <img src="../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true">
        BACK TO HOME
      </a>

      <div class="auth-form-wrapper">
        <div class="auth-header">
          <h1>Welcome Back</h1>
          <p>Sign in to continue your eco-friendly journey</p>
        </div>

        <form id="loginForm">
          <div class="login-role-switch">
            <button type="button" class="login-role-switch-button active" data-role="rider" id="riderToggle">RIDER LOGIN</button>
            <button type="button" class="login-role-switch-button" data-role="driver" id="driverToggle">DRIVER LOGIN</button>
          </div>

          <div class="form-group">
            <label class="form-label" id="emailLabel">APU Email Address</label>
            <div class="input-wrapper">
              <img src="../public-assets/icons/mail.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true">
              <input type="email" class="form-input form-input-icon" id="loginEmail" placeholder="example@apu.edu.my" required>
            </div>
          </div>

          <div class="form-group">
            <div class="form-label-row">
              <label class="form-label">Password</label>
            </div>
            <div class="input-wrapper">
              <img src="../public-assets/icons/lock.svg" width="20" height="20" class="input-icon icon-img" alt="" aria-hidden="true">
              <input type="password" class="form-input form-input-icon" id="loginPassword" placeholder="********" required>
            </div>
          </div>

          <button type="submit" class="login-submit-button" id="loginBtn">
            LOG IN AS <span id="roleText">RIDER</span>
            <img src="../public-assets/icons/arrow-right.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true">
          </button>

          <div class="auth-footer-text">
            <p>Don't have an account? <a href="register.php"><strong>Sign up now</strong></a></p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../public-assets/script.js"></script>
  <script src="auth.js"></script>
</body>

</html>
