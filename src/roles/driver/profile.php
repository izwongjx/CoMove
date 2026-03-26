<?php
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.html');
    exit();
}
$driver_id = $_SESSION['user_id'];


// -------- first cont profile -------- //
$sql_driver = "SELECT name, email, phone_number, created_at, plate_number FROM driver WHERE driver_id = ?";
$stmt_driver = mysqli_prepare($dbConn, $sql_driver);
mysqli_stmt_bind_param($stmt_driver, "i", $driver_id);
mysqli_stmt_execute($stmt_driver);
$result_driver = mysqli_stmt_get_result($stmt_driver);
$driver = mysqli_fetch_assoc($result_driver);
$join_date = isset($driver['created_at']) ? date("Y M", strtotime($driver['created_at'])) : "N/A";


// total trips / bottom cont // 
$sql_stats = "SELECT COUNT(*) as total_trips FROM trip WHERE driver_id = ? AND trip_status = 'completed'";
$stmt_stats = mysqli_prepare($dbConn, $sql_stats);
mysqli_stmt_bind_param($stmt_stats, 'i', $driver_id);
mysqli_stmt_execute($stmt_stats);
$result_stats = mysqli_stmt_get_result($stmt_stats);
$stats = mysqli_fetch_assoc($result_stats);
$total_trips = $stats['total_trips'] ?? 0;


// update form (popup)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];

    $sql_update = "UPDATE driver SET name = ?, email = ?, phone_number = ? WHERE driver_id = ?";
    $stmt_update = mysqli_prepare($dbConn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "sssi", $name, $email, $phone, $driver_id);
    mysqli_stmt_execute($stmt_update);

    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoRide - Driver Profile</title>
  <link rel="stylesheet" href="../../public-assets/style.css">
  <link rel="stylesheet" href="profile.css">
</head>

<body>
  <nav class="mainNav">
      <div class="insideNav">
        <a href="dashboard.php" class="logo">ECO<span>RIDE</span></a>
        <div class="navContents">
          <a href="dashboard.php" class="navContent"><img src="../../public-assets/icons/home.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Dashboard</a>
          <a href="my-rides.php" class="navContent"><img src="../../public-assets/icons/car.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> My Rides</a>
          <a href="earnings.php" class="navContent"><img src="../../public-assets/icons/dollar-sign.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Earnings</a>
          <a href="vehicle.php" class="navContent"><img src="../../public-assets/icons/file-text.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Vehicle</a>
          <a href="profile.php" class="currentNav"><img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile</a>
        </div>
        <div class="nav-actions"><a href="../../../index.html" class="nav-logout" title="Log out"><img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></a></div>
      </div>
  </nav>

  <div id="editProfileModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2 class="offerRideHeading">EDIT PROFILE</h2>
      <br>
      <form method="POST" action="profile.php">
        <input type="text" name="name" 
               value="<?php echo htmlspecialchars($driver['name'] ?? ''); ?>" 
               placeholder="Full Name" required>

        <input type="email" name="email" 
               value="<?php echo htmlspecialchars($driver['email'] ?? ''); ?>" 
               placeholder="Email Address" required>

        <input type="text" name="phone_number" 
               value="<?php echo htmlspecialchars($driver['phone_number'] ?? ''); ?>" 
               placeholder="Phone Number" required>

        <button type="submit" name="update_profile" class="createTrip">SAVE CHANGES</button>
      </form>
    </div>
  </div>


  <main class="dashboardMain">
      <h1 class="pageTitle">MY PROFILE</h1>
      <p class="pageSubtitle">Manage your driver account</p>

      <div class="firstCont">
        <div class="firstContContent">
          
          <div class="firstContLeftSide">
            <img src="../../public-assets/icons/user.svg" alt="Profile Avatar" class="profile-pic">
            <div class="profile-stats">
              <p class="name"><?php echo htmlspecialchars($driver['name'] ?? 'Driver'); ?></p>
              
              <p class="verified">Driver - Verified</p>
              
              <p class="totalRide"><?php echo $total_trips; ?> Rides</p>
            </div>
          </div>

          <div class="firstContRightSide">
            <div class="contactInfo">
              <img src="../../public-assets/icons/mail.svg" alt="Email Icon">
              <span><?php echo htmlspecialchars($driver['email'] ?? 'N/A'); ?></span>
            </div>
            
            <div class="contactInfo">
              <img src="../../public-assets/icons/phone.svg" alt="Phone Icon">
              <span><?php echo htmlspecialchars($driver['phone_number'] ?? 'N/A'); ?></span>
            </div>
            
            <div class="contactInfo">
              <img src="../../public-assets/icons/car.svg" alt="Car Icon">
              <span>Plate: <?php echo htmlspecialchars($driver['plate_number'] ?? 'N/A'); ?></span> 
            </div>

            <div class="editProfileButton">
              <button id="editProfileBtn">
                <img src="../../public-assets/icons/edit.svg" alt="Edit Icon">
                Edit Profile
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="firstCont">
          <h2 class="ratingsCont">
          <img src="../../public-assets/icons/stats.png" alt="" srcset="">DRIVER STATS</h2>

          <div class="lastContContent">
            <div class="lastFirstRow">
              <div class="ratingCards">
                <p>TOTAL TRIPS</p>
                <p class="star"><?php echo $total_trips; ?></p>
              </div>
              <div class="ratingCards">
                <p>MEMBER SINCE</p>
                <p class="star"><?php echo strtoupper($join_date); ?></p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="dltAcct">
          <button class="dltAcct" id="deleteAccountBtn" onclick="confirmDelete()">Delete Account</button>
        </div>
        
        <script>
          function confirmDelete() {
            const result = confirm("Are you sure you want to delete your EcoRide account? This cannot be undone.");
            if (result) {
              window.location.href = 'delete_account.php'; 
            }
          }
        </script>
        
  </main>

  <nav class="bottom-nav driver-bottom-nav">
    <a href="dashboard.php"><img src="../../public-assets/icons/home.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="my-rides.php"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="earnings.php"><img src="../../public-assets/icons/dollar-sign.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="vehicle.php"><img src="../../public-assets/icons/file-text.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="profile.php" class="active" ><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
  </nav>

  <script src="../../public-assets/script.js"></script>
  <script src="profile.js"></script>
</body>
</html>