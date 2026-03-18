<?php
require_once __DIR__ . '/bootstrap.php';
adminRequireAccess();

$pageTitle = isset($pageTitle) ? $pageTitle : 'Admin';
$activePage = isset($activePage) ? $activePage : '';

function adminNavClass(string $page, string $activePage): string
{
    return $page === $activePage ? 'nav-item active' : 'nav-item';
}

function adminBottomNavClass(string $page, string $activePage): string
{
    return $page === $activePage ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo adminEscape($pageTitle); ?> - EcoRide Admin</title>
  <link rel="stylesheet" href="../../public-assets/style.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body>
  <nav class="top-nav admin-nav-bg">
    <div class="nav-inner">
      <a href="dashboard.php" class="logo">ECO<span>RIDE</span> <span>Admin</span></a>
      <div class="nav-items">
        <a href="dashboard.php" class="<?php echo adminNavClass('dashboard', $activePage); ?>"><img src="../../public-assets/icons/layout-dashboard.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Overview</a>
        <a href="users.php" class="<?php echo adminNavClass('users', $activePage); ?>"><img src="../../public-assets/icons/users.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Users</a>
        <a href="rewards.php" class="<?php echo adminNavClass('rewards', $activePage); ?>"><img src="../../public-assets/icons/gift.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Rewards</a>
        <a href="logs.php" class="<?php echo adminNavClass('logs', $activePage); ?>"><img src="../../public-assets/icons/activity.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Logs</a>
        <a href="settings.php" class="<?php echo adminNavClass('settings', $activePage); ?>"><img src="../../public-assets/icons/settings.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Settings</a>
        <a href="profile.php" class="<?php echo adminNavClass('profile', $activePage); ?>"><img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile</a>
      </div>
      <a href="../../../index.html" class="nav-logout"><img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></a>
    </div>
  </nav>
