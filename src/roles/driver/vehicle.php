  <?php
  session_start();
  require_once '../../config/conn.php';

  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
      header('Location: ../../../index.html');
      exit();
  }

  $driver_id = $_SESSION['user_id'];

  // ---------- fetch vehicle details ---------- //
  $sql_vehicle = "SELECT vehicle_model, plate_number, color FROM driver WHERE driver_id = ?";
  $stmt_vehicle = mysqli_prepare($dbConn, $sql_vehicle);
  mysqli_stmt_bind_param($stmt_vehicle, "i", $driver_id);
  mysqli_stmt_execute($stmt_vehicle);
  $result_vehicle = mysqli_stmt_get_result($stmt_vehicle);
  $vehicle = mysqli_fetch_assoc($result_vehicle);


  // ---------- update vehicle form ---------- //
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $vehicle_model = $_POST['vehicle_model'];
      $plate_number  = $_POST['plate_number'];
      $color         = $_POST['color'];

      $sql_update = "UPDATE driver SET vehicle_model = ?, plate_number = ?, color = ? WHERE driver_id = ?";
      $stmt_update = mysqli_prepare($dbConn, $sql_update);
      // 's' = string, 'i' = integer
      // sssi = vehicle_model(s), plate_number(s), color(s), driver_id(i)
      mysqli_stmt_bind_param($stmt_update, "sssi", $vehicle_model, $plate_number, $color, $driver_id);
      mysqli_stmt_execute($stmt_update);

      header('Location: vehicle.php');
      exit();
  }


// update form
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
    <title>EcoRide - Vehicle Information</title>
    <link rel="stylesheet" href="../../public-assets/style.css">
    <link rel="stylesheet" href="vehicle.css">
  </head>

  <body>
    <nav class="mainNav">
      <div class="insideNav">
        <a href="dashboard.php" class="logo">ECO<span>RIDE</span></a>
        <div class="navContents">
          <a href="dashboard.php" class="navContent">
            <img src="../../public-assets/icons/home.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true">
            Dashboard
          </a>
          <a href="my-rides.php" class="navContent">
            <img src="../../public-assets/icons/car.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> My Rides
          </a>
          <a href="earnings.php" class="navContent">
            <img src="../../public-assets/icons/dollar-sign.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Earnings
          </a>
          <a href="vehicle.php" class="currentNav">
            <img src="../../public-assets/icons/file-text.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Vehicle
          </a>
          <a href="profile.php" class="navContent">
            <img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile
          </a>
        </div>
        <div class="nav-actions">
          <a href="../../../index.html" class="nav-logout" title="Log out">
            <img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true">
          </a>
        </div>
      </div>
    </nav>

    <div id="updateVehicleModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <h2 class="offerRideHeading">UPDATE VEHICLE INFO</h2>
        <br>

        <form method="POST" action="vehicle.php">

          <input type="text" name="vehicle_model"
                value="<?php echo htmlspecialchars($vehicle['vehicle_model']); ?>"
                placeholder="Vehicle Model (e.g. Toyota Prius)" required>

          <input type="text" name="plate_number"
                value="<?php echo htmlspecialchars($vehicle['plate_number']); ?>"
                placeholder="License Plate (e.g. ABC 1234)" required>

          <input type="text" name="color"
                value="<?php echo htmlspecialchars($vehicle['color']); ?>"
                placeholder="Color (e.g. Silver)" required>

          <button type="submit" class="createTrip">SAVE CHANGES</button>
        </form>
      </div>
    </div>

    <!-- MAIN CONTENT -->
    <main class="dashboardMain">
      <div class="headerCont">
        <h1 class="pageTitle">VEHICLE INFORMATION</h1>
        <p class="pageSub">Manage your vehicle details and documents</p>
      </div>

      <div class="vehicleDetailsCont">
        <div class="vehicleDetails">
          <div class="vehicleDetailsLEFT">
            <img src="../../public-assets/icons/car.svg" alt="" srcset="">
            <h1 id="details">VEHICLE DETAILS</h1>
          </div>
          <div class="vehicleDetailsRIGHT">
            <p>VERIFIED</p>
          </div>
        </div>

        <div class="firstRow">
          <div>
            <div class="placeholder">VEHICLE MODEL</div>
            <div class="vehicleModel"><?php echo htmlspecialchars($vehicle['vehicle_model']); ?></div>
          </div>

          <div>
            <div class="placeholder">LICENSE PLATE</div>
            <div class="Plate"><?php echo htmlspecialchars($vehicle['plate_number']); ?></div>
          </div>

          <div>
            <div class="placeholder">COLOR</div>
            <div class="vehicleColour"><?php echo htmlspecialchars($vehicle['color']); ?></div>
          </div>
        </div>
      </div>

      <div class="documentSection">
        <div class="docTitle">
          <img src="../../public-assets/icons/doc.png" alt="documents icon">
          <h2>DOCUMENTS</h2>
        </div>

        <div class="docCont">
          <div class="docCard">
            <div class="docLeft">
              <img src="../../public-assets/icons/doc.png" alt="">
              <div class="docText">
                <p class="docName">Driver's License</p>
                <p class="docExpire">Expires Dec 2025</p>
              </div>
            </div>
            <span class="docStatus">VALID</span>
          </div>

          <div class="docCard">
            <div class="docLeft">
              <img src="../../public-assets/icons/doc.png" alt="">
              <div class="docText">
                <p class="docName">Insurance Policy</p>
                <p class="docExpire">Expires Aug 2024</p>
              </div>
            </div>
            <span class="docStatus">VALID</span>
          </div>

          <div class="docCard">
            <div class="docLeft">
              <img src="../../public-assets/icons/doc.png" alt="">
              <div class="docText">
                <p class="docName">Road Tax</p>
                <p class="docExpire">Expires Aug 2025</p>
              </div>
            </div>
            <span class="docStatus">VALID</span>
          </div>

          <div class="docCard">
            <div class="docLeft">
              <img src="../../public-assets/icons/doc.png" alt="">
              <div class="docText">
                <p class="docName">Vehicle Ownership Cert</p>
                <p class="docExpire">Verified</p>
              </div>
            </div>
            <span class="docStatus">VALID</span>
          </div>
        </div>
      </div>

      <div class="updateVehicleInfo">
        <!-- id="updateVehicleBtn" is what the JS will target to open the modal -->
        <button class="update" id="updateVehicleBtn">
          <img src="../../public-assets/icons/edit.svg" alt="">
          Update Vehicle Info
        </button>
      </div>

    </main>

    <nav class="bottom-nav driver-bottom-nav">
      <a href="dashboard.php"><img src="../../public-assets/icons/home.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
      <a href="my-rides.php"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
      <a href="earnings.php"><img src="../../public-assets/icons/dollar-sign.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
      <a href="vehicle.php" class="active"><img src="../../public-assets/icons/file-text.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
      <a href="profile.php"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    </nav>

    <script src="../../public-assets/script.js"></script>
    <script src="vehicle.js"></script>
  </body>

  </html>







