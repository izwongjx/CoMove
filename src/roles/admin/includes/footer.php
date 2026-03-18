  <div class="toast-container" id="toastContainer"></div>

  <nav class="bottom-nav admin-bottom-nav">
    <a href="dashboard.php" class="<?php echo adminBottomNavClass('dashboard', $activePage); ?>"><img src="../../public-assets/icons/layout-dashboard.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="users.php" class="<?php echo adminBottomNavClass('users', $activePage); ?>"><img src="../../public-assets/icons/users.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="rewards.php" class="<?php echo adminBottomNavClass('rewards', $activePage); ?>"><img src="../../public-assets/icons/gift.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="logs.php" class="<?php echo adminBottomNavClass('logs', $activePage); ?>"><img src="../../public-assets/icons/activity.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="settings.php" class="<?php echo adminBottomNavClass('settings', $activePage); ?>"><img src="../../public-assets/icons/settings.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="profile.php" class="<?php echo adminBottomNavClass('profile', $activePage); ?>"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
  </nav>

  <script src="admin.js"></script>
  <script src="../../public-assets/script.js"></script>
</body>
</html>
