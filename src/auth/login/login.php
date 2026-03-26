<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Choose Login Role</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="login.css">
</head>

<body>
  <main class="page">
    <aside class="hero" aria-label="CoMove Message">
      <div class="hero-content">
        <h2>WELCOME<br><span>BACK</span></h2>
        <p>Pick how you want to ride with the CoMove community today.</p>
      </div>
    </aside>

    <section class="panel">
      <nav class="top-nav" aria-label="Page Navigation">
        <a href="../../../index.php" class="back-link">
          <img src="../../public-assets/icons/arrow-left.svg" width="16" height="16" class="icon-img" alt=""
            aria-hidden="true"> BACK TO HOME
        </a>
      </nav>

      <div class="form-wrapper">
        <header class="section-header">
          <h1>Choose Your Role</h1>
          <p>Select the account type you want to sign in with.</p>
        </header>

        <div class="role-list" role="list">
          <a class="role-card" role="listitem" href="login-as-rider.php">
            <span class="role-icon" aria-hidden="true">
              <img src="../../public-assets/icons/user.svg" width="24" height="24" alt="">
            </span>
            <span class="role-text">Log in as Rider</span>
          </a>

          <a class="role-card" role="listitem" href="login-as-driver.php">
            <span class="role-icon" aria-hidden="true">
              <img src="../../public-assets/icons/car.svg" width="24" height="24" alt="">
            </span>
            <span class="role-text">Log in as Driver</span>
          </a>
        </div>
      </div>
    </section>
  </main>

  <script src="../../public-assets/script.js"></script>
</body>

</html>
