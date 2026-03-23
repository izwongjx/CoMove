<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comove – Rewards</title>
  <link rel="icon" type="image/svg+xml" href="../../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="rider.css">
</head>
<body>
  <nav class="top-nav rider-nav-bg">
    <div class="nav-inner">
      <a href="../../index.php" class="logo">Co<span>move</span></a>
      <div class="nav-items">
        <a href="dashboard.php" class="nav-item"><img src="icons/home.svg" width="16" height="16" class="icon-img" alt=""> Dashboard</a>
        <a href="find-rides.php" class="nav-item"><img src="icons/search.svg" width="16" height="16" class="icon-img" alt=""> Find Rides</a>
        <a href="my-trips.php" class="nav-item"><img src="icons/map.svg" width="16" height="16" class="icon-img" alt=""> My Trips</a>
        <a href="friends.php" class="nav-item"><img src="icons/users.svg" width="16" height="16" class="icon-img" alt=""> Friends</a>
        <a href="rewards.php" class="nav-item active"><img src="icons/gift.svg" width="16" height="16" class="icon-img" alt=""> Rewards</a>
        <a href="profile.php" class="nav-item"><img src="icons/user.svg" width="16" height="16" class="icon-img" alt=""> Profile</a>
      </div>
      <div class="nav-actions">
        <a href="../../index.php" class="nav-logout-btn"><img src="icons/log-out.svg" width="16" height="16" class="icon-img" alt=""> Log out</a>
      </div>
    </div>
  </nav>

  <main class="dashboard-main">
    <h1 class="page-title">Green Points</h1>
    <p class="page-subtitle">Redeem your points for great rewards</p>

    <!-- Points Hero -->
    <div class="points-hero">
      <div class="points-big" id="totalPoints">0</div>
      <div class="points-label">Total Green Points</div>
      <div class="progress-label" id="rewardsProgressLabel">Loading points...</div>
    </div>

    <!-- Redeem Rewards -->
    <div class="section-title">Redeem Rewards</div>
    <div id="rewardsList"></div>

    <!-- Points History -->
    <div class="section-title" style="margin-top:28px;">Points History</div>
    <div class="form-card" style="padding:0;overflow:hidden;" id="pointsHistory"></div>
  </main>

  <nav class="bottom-nav rider-nav-bg">
    <a href="dashboard.php"><img src="icons/home.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="find-rides.php"><img src="icons/search.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="my-trips.php"><img src="icons/map.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="friends.php"><img src="icons/users.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="rewards.php" class="active"><img src="icons/gift.svg" width="24" height="24" class="icon-img" alt=""></a>
    <a href="profile.php"><img src="icons/user.svg" width="24" height="24" class="icon-img" alt=""></a>
  </nav>
  <div class="toast" id="toast"></div>
  <script src="script.js"></script>
  <script src="rewards.js"></script>
</body>
</html>

