<?php
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.html');
    exit();
}
$driver_id = $_SESSION['user_id'];


// ---------------- QUERY 1 TOTAL EARNINGS -------------- //
$sql_total = "SELECT SUM(total_amount) AS total_earnings FROM trip WHERE driver_id = ? AND trip_status = 'completed'";
$stmt_total = mysqli_prepare($dbConn, $sql_total);
mysqli_stmt_bind_param($stmt_total, 'i', $driver_id);
mysqli_stmt_execute($stmt_total);

$result_total = mysqli_stmt_get_result($stmt_total);
$row_total = mysqli_fetch_assoc($result_total);
$total_earnings = $row_total['total_earnings'] ?? 0;


// ---------------- QUERY 2: THIS WEEK EARNINGS -------------- //
$sql_week = "SELECT SUM(total_amount) AS week_earnings FROM trip WHERE driver_id = ? AND trip_status = 'completed' AND YEARWEEK(departure_time, 1) = YEARWEEK(NOW(), 1)";

$stmt_week = mysqli_prepare($dbConn, $sql_week);
mysqli_stmt_bind_param($stmt_week, 'i', $driver_id);
mysqli_stmt_execute($stmt_week);

$result_week = mysqli_stmt_get_result($stmt_week);
$row_week = mysqli_fetch_assoc($result_week);
$week_earnings = $row_week['week_earnings'] ?? 0;

// ---------------- QUERY 3: TOTAL TRIPS -------------- //
$sql_stats = "SELECT COUNT(*) AS total_trips FROM trip WHERE driver_id = ? AND trip_status = 'completed'";
$stmt_stats = mysqli_prepare($dbConn, $sql_stats);
mysqli_stmt_bind_param($stmt_stats, 'i', $driver_id);
mysqli_stmt_execute($stmt_stats);
$result_stats = mysqli_stmt_get_result($stmt_stats);
$row_stats = mysqli_fetch_assoc($result_stats);
$total_trips = $row_stats['total_trips'];

// fetch member since 
$sql_driver = "SELECT created_at FROM driver WHERE driver_id = ?";
$stmt_driver = mysqli_prepare($dbConn, $sql_driver);
mysqli_stmt_bind_param($stmt_driver, 'i', $driver_id);
mysqli_stmt_execute($stmt_driver);
$result_driver = mysqli_stmt_get_result($stmt_driver);
$driver_info = mysqli_fetch_assoc($result_driver);
$driver_since = date('M Y', strtotime($driver_info['created_at']));

// ---------- total trips -------------- //
$sql_history = "SELECT start_location, end_location, departure_time, total_amount 
                FROM trip 
                WHERE driver_id = ? AND trip_status = 'completed' 
                ORDER BY departure_time DESC";

$stmt_history = mysqli_prepare($dbConn, $sql_history);
mysqli_stmt_bind_param($stmt_history, 'i', $driver_id);
mysqli_stmt_execute($stmt_history);
$result_history = mysqli_stmt_get_result($stmt_history);

// empty array to store completed trips
$completed_trips = [];
while($row = mysqli_fetch_assoc($result_history)){
  $completed_trips[] = $row;
}



// sql_xxx
// stmt_xxx (dbconn, sql_xxx)
// mysqli_stmt_bind_param(stmt_xxx, 'i', driver_id)
// mysqli_stmt_execute(stmt_xxx)
// result_xxx = mysqli_stmt_get_result(stmt_xxx)
// row_xxx = mysqli_fetch_assoc(result_xxx)

?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EcoRide - Driver Earnings</title>
  <link rel="stylesheet" href="../../public-assets/style.css">
  <link rel="stylesheet" href="earnings.css">
</head>

<body>
  <nav class="mainNav">
      <div class="insideNav">
        <a href="dashboard.php" class="logo">ECO<span>RIDE</span></a>
        <div class="navContents">
          <a href="dashboard.php" class="navContent"><img src="../../public-assets/icons/home.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Dashboard</a>
          <a href="my-rides.php" class="navContent"><img src="../../public-assets/icons/car.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> My Rides</a>
          <a href="earnings.php" class="currentNav"><img src="../../public-assets/icons/dollar-sign.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Earnings</a>
          <a href="vehicle.php" class="navContent"><img src="../../public-assets/icons/file-text.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Vehicle</a>
          <a href="profile.php" class="navContent"><img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile</a>
        </div>
        <div class="nav-actions"><a href="../../../index.html" class="nav-logout" title="Log out"><img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></a></div>
      </div>   
    </nav>

  <main class="dashboardMain">
    <div class="firstContainer">
      <div class="heading">
        <h1 class="pageTitle">EARNINGS</h1>
        <p class="pageSub">Track your income and trip history</p>
      </div>
    </div>

    <div class="secondContainer">
      <div class="totalEarn">
        <div class="content">
          <p class="balance">TOTAL EARNINGS</p>
          <p class="amount">RM <?php echo number_format($total_earnings, 2); ?></p>
          <p class="withdrawal">Available for Withdrawal</p>
        </div>
        <div class="blackicon">
          <img src="../../public-assets/icons/dollar-sign.svg" alt="" srcset="">
        </div>
      </div>

      <div class="stats">
        <div class="content">
          <p class="thisweek">THIS WEEK</p>
          <p class="statsEarn">RM <?php echo number_format($week_earnings, 2); ?></p>
          <p class="increaseStat">Active This Week</p>          
        </div>
        <div class="icon">
          <img src="../../public-assets/icons/stats.png" alt="" srcset="">
        </div>
      </div>

      <div class="trips">
        <div class="content">
          <p class="totalTrip">TOTAL TRIPS</p>
          <p class="tripAMT"><?php echo $total_trips; ?></p>
          <p class="since">Since <?php echo $driver_since; ?></p>
        </div>
        <div class="icon">
          <img src="../../public-assets/icons/calendar.svg" alt="" srcset="">
        </div>
      </div>
    </div>
    
    <div class="completedTripContainer">
      <h2 class="CompleteTripTitle">COMPLETED TRIPS</h2>

      <div class="COMPtrips">
        <?php if (empty($completed_trips)): ?>
            <p style="color: #777; padding: 20px;">No completed trips yet.</p>
        <?php else: ?>
            <?php foreach ($completed_trips as $trip): ?>
            <div class="tripRow">
                <div class="tripLeftSide">
                    <div class="moneyIcon">$</div>
                    <div class="tripInfo">
                        <div class="route">
                            <?php echo htmlspecialchars($trip['start_location']); ?> - <?php echo htmlspecialchars($trip['end_location']); ?>
                        </div>
                        <div class="time">
                            <?php echo date('M d, g:i A', strtotime($trip['departure_time'])); ?>
                        </div>
                    </div>
                </div>
                <div class="tripRightSide">
                    <div class="price">+RM <?php echo number_format($trip['total_amount'], 2); ?></div>
                    <span class="status">Completed</span>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
      </div>

  </main>

  <nav class="bottom-nav driver-bottom-nav">
    <a href="dashboard.php"><img src="../../public-assets/icons/home.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="my-rides.php"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="earnings.php" class="active"><img src="../../public-assets/icons/dollar-sign.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="vehicle.php"><img src="../../public-assets/icons/file-text.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="profile.php"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
  </nav>

  <script src="../../public-assets/script.js"></script>
  <script src="earnings.js"></script>
</body>
</html>







