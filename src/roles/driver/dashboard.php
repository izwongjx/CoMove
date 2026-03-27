<!-- PREP, BIND, EXEC
PREP THE QUERY WITH EMPTY SLOTS SO THE DB UNDERSTAND THE STRUCTURE
BIND IS PUTTING THE ACTUAL VALUES INTO THE SLOTS
EXECUTE IS RUNNING THE QUERY -->


<?php
session_start();
require_once '../../config/conn.php';

//  check if the user aka driver is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.php');
    exit();
}

$driver_id = $_SESSION['user_id'];



//---------------- QUERY 1: Total Rides ---------------//
$sql_rides = "SELECT COUNT(*) AS total_rides FROM trip WHERE driver_id = ? AND trip_status = 'completed'";

// get the sql ready for db again
$stmt_rides = mysqli_prepare($dbConn, $sql_rides);

// now it puts the value into the ? to prevent sql injection
mysqli_stmt_bind_param($stmt_rides, 'i', 
  $driver_id
);

// execute the statement
mysqli_stmt_execute($stmt_rides);

// finally fetching the result 
$result_rides = mysqli_stmt_get_result($stmt_rides);
$row_rides = mysqli_fetch_assoc($result_rides);
$total_rides = $row_rides['total_rides'];



// ---------------- QUERY 2: Total Earnings ---------------//
$sql_earnings = "SELECT SUM(total_amount) AS total_earnings FROM trip WHERE driver_id = ? AND trip_status = 'completed'";

// prep phase
$stmt_earnings = mysqli_prepare($dbConn, $sql_earnings);

// bind phase
mysqli_stmt_bind_param($stmt_earnings, 'i', 
  $driver_id
);

// execute phase
mysqli_stmt_execute($stmt_earnings);

// fetch phase
$result_earnings = mysqli_stmt_get_result($stmt_earnings);
$row_earnings = mysqli_fetch_assoc($result_earnings);
$total_earnings = $row_earnings['total_earnings'] ?? 0;



// ---------- auto update to check if any ongoing trip ---------------//
$sql_auto = "UPDATE trip SET trip_status = 'ongoing' WHERE driver_id = ? AND trip_status = 'scheduled' AND departure_time <= NOW()";
$stmt_auto = mysqli_prepare($dbConn, $sql_auto);
mysqli_stmt_bind_param($stmt_auto, 'i', $driver_id);
mysqli_stmt_execute($stmt_auto);


// ---------------- QUERY 3: FETCH ONGOING TRIPS ---------------//
$sql_ongoing = "SELECT * FROM trip WHERE driver_id = ? AND trip_status = 'ongoing' LIMIT 1";
$stmt_ongoing = mysqli_prepare($dbConn, $sql_ongoing);
mysqli_stmt_bind_param($stmt_ongoing, 'i', $driver_id);
mysqli_stmt_execute($stmt_ongoing);

// fetch result and store in array to access values by column name
$result_ongoing = mysqli_stmt_get_result($stmt_ongoing);
$ongoing_trip = mysqli_fetch_assoc($result_ongoing);


// ---------------- QUERY 4: FETCH PASSengers -------------- // 
// this joined 2 tables to get passanger name and payment method for ongoing trip
$passengers = [];
if ($ongoing_trip) {
    $trip_id = $ongoing_trip['trip_id'];
    $sql_passengers = "SELECT rr.request_id, rr.amount_paid, rr.payment_method, 
                              r.name AS rider_name
                       FROM ride_request rr
                       JOIN rider r ON rr.rider_id = r.rider_id
                       WHERE rr.trip_id = ? AND rr.request_status = 'approved'";

    $stmt_passengers = mysqli_prepare($dbConn, $sql_passengers);
    mysqli_stmt_bind_param($stmt_passengers, 'i', $trip_id);
    mysqli_stmt_execute($stmt_passengers);
    $result_passengers = mysqli_stmt_get_result($stmt_passengers);
    while ($row = mysqli_fetch_assoc($result_passengers)) {
        $passengers[] = $row;
    }
}


// ---------------- Incoming Requests ---------------//
$sql_requests = "SELECT rr.request_id, rr.amount_paid, rr.payment_method,
                        r.name AS rider_name, 
                        t.start_location, t.end_location, t.departure_time
                FROM ride_request rr
                JOIN rider r ON rr.rider_id = r.rider_id
                JOIN trip t ON rr.trip_id = t.trip_id
                WHERE t.driver_id = ? 
                  AND rr.request_status = 'pending'
                  AND t.trip_status = 'scheduled'
                ORDER BY t.departure_time ASC";

$stmt_requests = mysqli_prepare($dbConn, $sql_requests);
mysqli_stmt_bind_param($stmt_requests, 'i', $driver_id);
mysqli_stmt_execute($stmt_requests);
$result_requests = mysqli_stmt_get_result($stmt_requests);

$pending_requests = [];
while($row = mysqli_fetch_assoc($result_requests)){
  $pending_requests[] = $row;
}


// ---------- VEHICLE INFO ONZ --------------//
$sql_vehicle = "SELECT vehicle_model, plate_number FROM driver WHERE driver_id = ?";
$stmt_vehicle = mysqli_prepare($dbConn, $sql_vehicle);
mysqli_stmt_bind_param($stmt_vehicle, 'i', $driver_id);
mysqli_stmt_execute($stmt_vehicle);
$result_vehicle = mysqli_stmt_get_result($stmt_vehicle);
$vehicle = mysqli_fetch_assoc($result_vehicle);
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Driver Dashboard</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="../../public-assets/style.css">
  <link rel="stylesheet" href="dashboard.css">
</head>

<body>
  <nav class="mainNav">
    <div class="insideNav">
      <a href="dashboard.php" class="logo">CO<span>MOVE</span></a>
      <div class="navContents">
        <a href="dashboard.php" class="currentNav"><img src="../../public-assets/icons/home.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true">
          Dashboard</a>
        <a href="my-rides.php" class="navContent"><img src="../../public-assets/icons/car.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> My Rides</a>
        <a href="earnings.php" class="navContent"><img src="../../public-assets/icons/dollar-sign.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Earnings</a>
        <a href="redemption.php" class="navContent"><img src="../../public-assets/icons/gift.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Redemption</a>
        <a href="vehicle.php" class="navContent"><img src="../../public-assets/icons/file-text.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Vehicle</a>
        <a href="profile.php" class="navContent"><img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile</a>
      </div>
      <div class="nav-actions"><a href="../../../index.php" class="nav-logout" title="Log out"><img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true"></a></div>
    </div>
  </nav>

  <main class="dashboardMain">
    <div class="dbHeader">
      <div class="titleContent">
        <h1 class="dbTitle">Driver <span class="dbTitle2">Dashboard</span></h1>
        <p class="dbSubtitle">Manage your rides and track your earnings.</p>
      </div>

      <div class="offerRideButton">
        <button>OFFER A RIDE</button>
      </div>
    </div>

    <!-- this is the pop up when press on offer ride -->
    <div id="offerRideModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2 class="offerRideHeading">OFFER A RIDE</h2>
        <br>

        <!-- connects to the offer-ride php -->
        <form method="POST" action="offer_ride.php">

          <input type="text" id="location" name="start_location" placeholder="Start Location" required>
          <input type="text" id="destination" name="end_location" placeholder="Destination" required>

          <div class="datetime">
            <input type="date" name="departure_date" required>
            <input type="time" name="departure_time" required>
          </div>

          <input type="text" id="time" name="estimated_duration" placeholder="Estimated Duration (e.g. 45)" required>

          <div class="tripsArea">
            <div class="tripItems">
              <label for="seats">Available Seats</label>
              <input type="number" id="seats" name="total_seats" value="4" required>
            </div>
            <div class="tripItems">
              <label for="price">Total Trip Price</label>
              <input type="number" id="price" name="total_amount" value="20" required>
            </div>
          </div>

          <button type="submit" class="createTrip">CREATE TRIP</button>

        </form>
      </div>
    </div>

    <div class="firstContainer">
      <div class="dbFirstOval">
        <div class="cardContent">
          <h2>TOTAL RIDES</h2>
          <p class="passengersNum"><?php echo $total_rides; ?></p>
          <p>Community members transported</p>
        </div>
        <div class="pasIcon">
          <img src="../../public-assets/icons/users.svg" alt="">
        </div>
      </div>
      
      <div class="dbSecondOval">
        <div class="cardContent">
          <h2>TOTAL EARNINGS</h2>
          <p class="earningNum"><?php echo number_format($total_earnings, 2); ?></p>
          <p>Fuel costs shared</p>
        </div>
        <div class="earningIcon">
          <img src="../../public-assets/icons/stats.png" alt="">
        </div>
      </div>
    </div>


    <div class="secondContainer">
      <div class="secondTitle">
        <h2 class="ongoingTrip">
          <img src="../../public-assets/icons/stats.png" class="ongoingIcon">
          ONGOING TRIPS
        </h2>
        <p class="progress">IN PROGRESS</p>
      </div>

      <!-- check if got any ongoing trip anot -->
      <?php if ($ongoing_trip): ?>
        <div class="ongoingDesign">
          <div class="ongoing1">
            <p class="tripStart">
              Started <?php echo date('g:i A', strtotime($ongoing_trip['departure_time'])); ?>
            </p>

            <div class="startingEndingLoc">
              <p class="startLoc"><?php echo $ongoing_trip['start_location']; ?> - </p>
              <p class="endLoc"><?php echo $ongoing_trip['end_location']; ?></p>
            </div>

            <p class="passengerPaymentsTitle">PASSENGERS PAYMENT</p>

            <div class="passengerList">
              <?php foreach ($passengers as $passenger): ?>
                <div class="passengerRow">
                  <div class="passengerInfo">
                    <img src="../../public-assets/icons/profileCircle.png">
                    <p class="passengerName"><?php echo $passenger['rider_name']; ?></p>
                  </div>
                  <p class="passengerPayment">
                    RM <?php echo number_format($passenger['amount_paid'], 2); ?> 
                    - <?php echo $passenger['payment_method']; ?>
                  </p>

                  <!-- upload proof form for each passenger -->
                    <form action="upload_proof.php" method="POST" enctype="multipart/form-data" class="uploadProofForm">
                      <input type="hidden" name="request_id" value="<?php echo $passenger['request_id']; ?>">
                      <input type="file" name="proof" accept="image/*"
                            id="proof_<?php echo $passenger['request_id']; ?>" style="display:none" required>
                      <button type="button"
                            onclick="document.getElementById('proof_<?php echo $passenger['request_id']; ?>').click()">
                        UPLOAD PROOF
                      </button>
                    </form>

                </div>
              <?php endforeach; ?>
            </div>


            <!-- linking to the complete_trip php page -->
            <form action="complete_trip.php" method="POST">
              <input type="hidden" name="trip_id" value="<?php echo $ongoing_trip['trip_id']; ?>">
              <button type="submit" class="completeTripBtn">COMPLETE TRIP</button>
            </form>

          </div>
        </div>

      <?php else: ?>
        <!-- No ongoing trip -->
        <div class="ongoingDesign">
          <p style="color: #777; text-align: center; padding: 20px;">
            No ongoing trips at the moment.
          </p>
        </div>
      <?php endif; ?>

    </div>


    <div class="thirdContainer">
      <div class="thirdTitle">
        <img src="../../public-assets/icons/bell.svg" alt="">
        <h2>Incoming Requests</h2>
      </div>

      <div class="requestContainer">

        <!-- check for request -->
        <?php if (empty($pending_requests)): ?>
          <p style="color: #777; text-align: center; padding: 20px;">
            No incoming requests at the moment.
          </p>

        <?php else: ?>
          <div class="firstReq">

            <?php foreach ($pending_requests as $req): ?>
              <div class="reqCard">
                <img src="../../public-assets/icons/user.svg" alt="">

                <p class="reqFirstName"><?php echo htmlspecialchars($req['rider_name']); ?></p>
                <p class="priceFirstName">RM <?php echo number_format($req['amount_paid'], 2); ?></p>

                <p class="time">
                  <?php echo date('D, g:i A', strtotime($req['departure_time'])); ?>
                </p>
                <p class="route">
                  <?php echo htmlspecialchars($req['start_location']); ?> 
                  - 
                  <?php echo htmlspecialchars($req['end_location']); ?>
                </p>

                <span class="paymentType <?php echo strtolower($req['payment_method']); ?>">
                  <?php echo strtoupper($req['payment_method']); ?> PAYMENT
                </span>

                <div class="acceptReject">
                  <!-- reject req -->
                  <form method="POST" action="incoming_request.php">
                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                    <input type="hidden" name="action" value="decline">
                    <button type="submit" class="decline">Decline</button>
                  </form>

                  <!-- accept req -->
                  <form method="POST" action="incoming_request.php">
                    <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit" class="accept">Accept</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div class="fourthTitle">
      <img src="../../public-assets/icons/car.svg" alt="">
      <h2>VEHICLE STATUS</h2>
    </div>

    <div class="fourthContainer">
      <div class="vehicleContent">
        <div class="vehicleTitle">
          <p class="myVehicle">My Vehicle</p>
          <div class="verifiedIcon">
            <p>Verified</p>
          </div>
        </div>

        <p class="modelPlace">MODEL</p>
        <p class="vehicleModel"><?php echo htmlspecialchars($vehicle['vehicle_model']); ?></p>

        <p class="platePlace">License Plate</p>
        <p class="vehiclePlate"><?php echo htmlspecialchars($vehicle['plate_number']); ?></p>

        <p class="documentPlace">Driver Status</p>
        <p class="uptoDate">VERIFIED</p>

      </div>
      <button class="updateVehicle" onclick="window.location.href='vehicle.php'">
        Update Vehicle Info
      </button>
    </div>
  </main>

  <nav class="bottom-nav driver-bottom-nav">
    <a href="dashboard.php" class="active"><img src="../../public-assets/icons/home.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="my-rides.php"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="earnings.php"><img src="../../public-assets/icons/dollar-sign.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="redemption.php"><img src="../../public-assets/icons/gift.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="vehicle.php"><img src="../../public-assets/icons/file-text.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="profile.php"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
  </nav>

  <script src="../../public-assets/script.js"></script>
  <script src="dashboard.js"></script>
</body>

</html>







