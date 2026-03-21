<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Sign Up</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="register.css">
</head>

<body>
  <main class="page">
    <aside class="hero" aria-label="CoMove Message">
      <div class="hero-content">
        <h2>JOIN THE<br><span>REVOLUTION</span></h2>
        <p>Every shared ride is a vote for a cleaner planet. Be part of the solution, not the pollution.</p>
      </div>
    </aside>

    <section class="panel">
      <nav class="top-nav" aria-label="Page Navigation">
        <a href="../../../index.php" class="back-link">
          <img src="../../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> BACK TO HOME
        </a>
      </nav>

      <div class="form-wrapper">
        <header class="section-header">
          <h1>Join CoMove</h1>
          <p>Choose how you want to contribute to a greener future</p>
        </header>

        <div class="role-list">
          <button class="role-card" onclick="window.location.href='register-as-rider.php'">
            <div class="role-icon"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></div>
            <span class="role-text">Register as a Rider</span>
            <img src="../../public-assets/icons/arrow-right.svg" width="20" height="20" class="role-arrow icon-img" alt="" aria-hidden="true">
          </button>
          <button class="role-card" onclick="window.location.href='register-as-driver.php'">
            <div class="role-icon"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></div>
            <span class="role-text">Register as a Driver</span>
            <img src="../../public-assets/icons/arrow-right.svg" width="20" height="20" class="role-arrow icon-img" alt="" aria-hidden="true">
          </button>
        </div>
        <footer class="section-footer">
          <p>Already have an account? <a href="../login/login.php"><strong>Log in</strong></a></p>
        </footer>
      </div>
    </section>
  </main>

  <script src="../../public-assets/script.js"></script>
</body>

</html>

