<?php
session_start();
include "../../config/conn.php";

$role = isset($_SESSION['role']) ? strtolower(trim((string) $_SESSION['role'])) : '';
$driverId = isset($_SESSION['user_id']) ? trim((string) $_SESSION['user_id']) : '';

if ($role !== 'driver' || $driverId === '') {
    echo "<script>alert('Please login as driver first.');";
    die("window.location.href='../../auth/login/login.php';</script>");
}

$driverIdSafe = mysqli_real_escape_string($dbConn, $driverId);

$driverStatusSql = "SELECT driver_status FROM DRIVER WHERE driver_id = '" . $driverIdSafe . "' LIMIT 1";
$driverStatusResult = mysqli_query($dbConn, $driverStatusSql);
if (!$driverStatusResult) {
    echo "<script>alert('Unable to verify your account right now.');";
    die("window.location.href='../../auth/login/login.php';</script>");
}

$driverStatusRow = mysqli_fetch_array($driverStatusResult);
mysqli_free_result($driverStatusResult);

$driverStatus = strtolower(trim((string) ($driverStatusRow['driver_status'] ?? '')));
if ($driverStatus !== 'active') {
    session_unset();
    session_destroy();
    echo "<script>alert('This driver account is currently banned. Please contact an admin.');";
    die("window.location.href='../../auth/login/login.php';</script>");
}

$viewTripId = isset($_GET['view']) ? trim((string) $_GET['view']) : '';
$deleteTripId = isset($_GET['delete']) ? trim((string) $_GET['delete']) : '';

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function buildProfileImageSrc($photoBlob): string
{
    if ($photoBlob === null || $photoBlob === '') {
        return "../../public-assets/icons/user.svg";
    }

    $mime = null;
    $header = substr($photoBlob, 0, 16);
    if (strncmp($header, "\x89PNG\r\n\x1a\n", 8) === 0) {
        $mime = 'image/png';
    } elseif (strncmp($header, "\xFF\xD8\xFF", 3) === 0) {
        $mime = 'image/jpeg';
    } elseif (strncmp($header, "GIF87a", 6) === 0 || strncmp($header, "GIF89a", 6) === 0) {
        $mime = 'image/gif';
    } elseif (strncmp($header, "RIFF", 4) === 0 && substr($header, 8, 4) === "WEBP") {
        $mime = 'image/webp';
    } elseif (substr($header, 4, 4) === "ftyp" && (substr($header, 8, 4) === "avif" || substr($header, 8, 4) === "avis")) {
        $mime = 'image/avif';
    }

    if ($mime === null && class_exists('finfo')) {
        static $finfo = null;
        if ($finfo === null) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        }
        if ($finfo) {
            $detected = $finfo->buffer($photoBlob);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }
    }

    if ($mime === null || $mime === '') {
        $mime = 'image/jpeg';
    }

    return "data:" . $mime . ";base64," . base64_encode($photoBlob);
}

if ($deleteTripId !== '') {
    $deleteIdSafe = mysqli_real_escape_string($dbConn, $deleteTripId);
    $checkSql = "SELECT trip_id FROM TRIP WHERE trip_id = '" . $deleteIdSafe . "' AND driver_id = '" . $driverIdSafe . "' LIMIT 1";
    $checkResult = mysqli_query($dbConn, $checkSql);

    if ($checkResult && mysqli_num_rows($checkResult) > 0) {
        mysqli_query($dbConn, "DELETE FROM RIDE_REQUEST WHERE trip_id = '" . $deleteIdSafe . "'");
        mysqli_query($dbConn, "DELETE FROM TRIP WHERE trip_id = '" . $deleteIdSafe . "' AND driver_id = '" . $driverIdSafe . "'");
        if (mysqli_affected_rows($dbConn) > 0) {
            echo "<script>alert('Ride deleted successfully.');";
            die("window.location.href='my-rides.php';</script>");
        }
    }

    if ($checkResult) {
        mysqli_free_result($checkResult);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CoMove - Driver My Rides</title>
  <link rel="icon" type="image/svg+xml" href="../../public-assets/icons/site-icon.svg">
  <link rel="stylesheet" href="../../public-assets/style.css">
  <link rel="stylesheet" href="my-rides.css">
</head>

<body>
  <nav class="mainNav">
    <div class="insideNav">
      <a href="dashboard.php" class="logo">ECO<span>RIDE</span></a>
      <div class="navContents">
        <a href="dashboard.php" class="navContent"><img src="../../public-assets/icons/home.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true">
          Dashboard</a>
        <a href="my-rides.php" class="currentNav"><img src="../../public-assets/icons/car.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> My Rides</a>
        <a href="earnings.php" class="navContent"><img src="../../public-assets/icons/dollar-sign.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Earnings</a>
        <a href="vehicle.php" class="navContent"><img src="../../public-assets/icons/file-text.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Vehicle</a>
        <a href="profile.php" class="navContent"><img src="../../public-assets/icons/user.svg" width="16" height="16" class="icon-img" alt="" aria-hidden="true"> Profile</a>
      </div>
      <div class="nav-actions">
        <a href="../../../index.php" class="nav-logout" title="Log out">
          <img src="../../public-assets/icons/log-out.svg" width="20" height="20" class="icon-img" alt="" aria-hidden="true">
        </a>
      </div>
    </div>
  </nav>

  <main class="dashboard-main" id="myRidesPage">
    <h1 class="page-title">My Offered Rides</h1>
    <p class="page-subtitle">
      Manage your published rides and passenger bookings.
    </p>

    <section class="my-rides-panel" aria-live="polite">
      <div class="my-rides-list">
        <?php
        if ($viewTripId !== '') {
            $viewTripSafe = mysqli_real_escape_string($dbConn, $viewTripId);
            $tripSql = "SELECT t.trip_id, t.start_location, t.end_location, t.departure_time, t.total_seats, t.estimated_duration, t.total_amount, d.vehicle_model
                        FROM TRIP t
                        LEFT JOIN DRIVER d ON t.driver_id = d.driver_id
                        WHERE t.trip_id = '" . $viewTripSafe . "' AND t.driver_id = '" . $driverIdSafe . "' AND t.trip_status <> 'completed'
                        AND t.departure_time > NOW()
                        LIMIT 1";
            $tripResult = mysqli_query($dbConn, $tripSql);

            if ($tripResult && ($tripRow = mysqli_fetch_array($tripResult))) {
                $booked = 0;
                $bookedSql = "SELECT SUM(seats_requested) AS booked FROM RIDE_REQUEST WHERE trip_id = '" . $viewTripSafe . "' AND request_status = 'approved'";
                $bookedResult = mysqli_query($dbConn, $bookedSql);
                if ($bookedResult && ($bookedRow = mysqli_fetch_array($bookedResult))) {
                    $booked = isset($bookedRow['booked']) ? (int) $bookedRow['booked'] : 0;
                }
                if ($bookedResult) {
                    mysqli_free_result($bookedResult);
                }

                $totalSeats = (int) $tripRow['total_seats'];
                $availableSeats = max($totalSeats - $booked, 0);
                $estimatedDuration = $tripRow['estimated_duration'];
                $estimatedText = ($estimatedDuration === null || $estimatedDuration === '')
                    ? '-'
                    : ((string) $estimatedDuration . ' mins');
                ?>
                <div class="ride-detail-view">
                  <div class="ride-detail-top-actions">
                    <a href="my-rides.php" class="driver-btn driver-btn-small driver-btn-muted">
                      <img src="../../public-assets/icons/arrow-left.svg" width="14" height="14" alt="" aria-hidden="true">
                      Back
                    </a>
                  </div>
                  <section class="ride-detail-panel">
                    <div class="ride-detail-block">
                      <h3 class="ride-detail-title">Trip Details</h3>
                      <table class="ride-detail-table">
                        <tbody>
                          <tr><th>Date Time</th><td><?php echo escapeHtml((string) $tripRow['departure_time']); ?></td></tr>
                          <tr><th>Pickup</th><td><?php echo escapeHtml((string) $tripRow['start_location']); ?></td></tr>
                          <tr><th>Dropoff</th><td><?php echo escapeHtml((string) $tripRow['end_location']); ?></td></tr>
                          <tr><th>Estimated Duration</th><td><?php echo escapeHtml($estimatedText); ?></td></tr>
                          <tr><th>Total Price</th><td><?php echo '$' . number_format((float) $tripRow['total_amount'], 2); ?></td></tr>
                          <tr><th>Available Place</th><td><?php echo $availableSeats . '/' . $totalSeats; ?></td></tr>
                        </tbody>
                      </table>
                    </div>

                    <div class="ride-detail-block">
                      <h3 class="ride-detail-title">Passenger List</h3>
                      <div class="passenger-table-wrap">
                        <table class="passenger-table">
                          <thead>
                            <tr>
                              <th>Profile</th>
                              <th>Name</th>
                              <th>Requested Seat</th>
                              <th>Phone Number</th>
                              <th>Email</th>
                              <th>Payment Method</th>
                            </tr>
                          </thead>
                          <tbody>
                          <?php
                          $passengerSql = "SELECT r.name, r.phone_number, r.email, r.profile_photo, rr.seats_requested, rr.payment_method
                                           FROM RIDE_REQUEST rr
                                           JOIN RIDER r ON rr.rider_id = r.rider_id
                                           WHERE rr.trip_id = '" . $viewTripSafe . "' AND rr.request_status = 'approved'
                                           ORDER BY rr.requested_at ASC";
                          $passengerResult = mysqli_query($dbConn, $passengerSql);
                          $hasPassenger = false;

                          if ($passengerResult) {
                              while ($passengerRow = mysqli_fetch_array($passengerResult)) {
                                  $hasPassenger = true;
                                  $name = isset($passengerRow['name']) ? (string) $passengerRow['name'] : '';
                                  $initials = 'NA';
                                  $nameParts = preg_split('/\s+/', trim($name));
                                  if (count($nameParts) === 1 && $nameParts[0] !== '') {
                                      $initials = strtoupper(substr($nameParts[0], 0, 2));
                                  } elseif (count($nameParts) > 1) {
                                      $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
                                  }

                                  $profilePhoto = isset($passengerRow['profile_photo']) ? $passengerRow['profile_photo'] : null;
                                  $profileImg = buildProfileImageSrc($profilePhoto);
                                  echo "<tr>";
                                  echo "<td data-label='Profile'><span class='profile-chip'><img src='" . escapeHtml($profileImg) . "' alt='Profile' width='28' height='28'></span></td>";
                                  echo "<td data-label='Name'>" . escapeHtml($name) . "</td>";
                                  echo "<td data-label='Requested Seat'>" . (int) $passengerRow['seats_requested'] . "</td>";
                                  echo "<td data-label='Phone Number'>" . escapeHtml((string) $passengerRow['phone_number']) . "</td>";
                                  echo "<td data-label='Email'>" . escapeHtml((string) $passengerRow['email']) . "</td>";
                                  echo "<td data-label='Payment Method'>" . escapeHtml((string) $passengerRow['payment_method']) . "</td>";
                                  echo "</tr>";
                              }
                              mysqli_free_result($passengerResult);
                          }

                          if (!$hasPassenger) {
                              echo "<tr><td colspan='6' class='passenger-empty'>No passengers yet.</td></tr>";
                          }
                          ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </section>
                </div>
                <?php
            } else {
                echo "<div class='my-rides-empty'><h2 class='my-rides-empty-title'>Ride not found</h2><p class='my-rides-empty-text'>Please select a ride from your list.</p></div>";
            }

            if ($tripResult) {
                mysqli_free_result($tripResult);
            }
        } else {
            $listSql = "SELECT t.trip_id, t.start_location, t.end_location, t.departure_time, t.total_seats, t.estimated_duration, t.total_amount
                        FROM TRIP t
                        WHERE t.driver_id = '" . $driverIdSafe . "' AND t.trip_status <> 'completed'
                        AND t.departure_time > NOW()
                        ORDER BY t.departure_time DESC";
            $listResult = mysqli_query($dbConn, $listSql);

            if (!$listResult || mysqli_num_rows($listResult) <= 0) {
                ?>
                <div class="my-rides-empty">
                  <img
                    src="../../public-assets/icons/car.svg"
                    width="32"
                    height="32"
                    class="my-rides-empty-icon"
                    alt=""
                    aria-hidden="true"
                  />
                  <h2 class="my-rides-empty-title">No rides published yet</h2>
                  <p class="my-rides-empty-text">
                    Your upcoming and completed offered rides will appear here.
                  </p>
                </div>
                <?php
            } else {
                ?>
                <div class="my-rides-table-wrap">
                  <table class="my-rides-table">
                    <thead>
                      <tr>
                        <th>Date Time</th>
                        <th>Location</th>
                        <th>Estimated Duration</th>
                        <th>Total Price</th>
                        <th>Available Place</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php
                    while ($row = mysqli_fetch_array($listResult)) {
                        $tripId = (string) $row['trip_id'];
                        $tripIdSafe = mysqli_real_escape_string($dbConn, $tripId);
                        $booked = 0;
                        $bookedSql = "SELECT SUM(seats_requested) AS booked FROM RIDE_REQUEST WHERE trip_id = '" . $tripIdSafe . "' AND request_status = 'approved'";
                        $bookedResult = mysqli_query($dbConn, $bookedSql);
                        if ($bookedResult && ($bookedRow = mysqli_fetch_array($bookedResult))) {
                            $booked = isset($bookedRow['booked']) ? (int) $bookedRow['booked'] : 0;
                        }
                        if ($bookedResult) {
                            mysqli_free_result($bookedResult);
                        }

                        $totalSeats = (int) $row['total_seats'];
                        $availableSeats = max($totalSeats - $booked, 0);
                        $estimatedDuration = $row['estimated_duration'];
                        $estimatedText = ($estimatedDuration === null || $estimatedDuration === '')
                            ? '-'
                            : ((string) $estimatedDuration . ' mins');

                        echo "<tr>";
                        echo "<td data-label='Date Time'>" . escapeHtml((string) $row['departure_time']) . "</td>";
                        echo "<td data-label='Location'>" . escapeHtml((string) $row['start_location']) . " -> " . escapeHtml((string) $row['end_location']) . "</td>";
                        echo "<td data-label='Estimated Duration'>" . escapeHtml($estimatedText) . "</td>";
                        echo "<td data-label='Total Price'>$" . number_format((float) $row['total_amount'], 2) . "</td>";
                        echo "<td data-label='Available Place'>" . $availableSeats . "/" . $totalSeats . "</td>";
                        echo "<td data-label='Action' class='rides-action-cell'>";
                        echo "<a href='my-rides.php?view=" . escapeHtml($tripId) . "' class='driver-btn driver-btn-small driver-btn-muted'>View</a>";
                        echo "<a href='my-rides.php?delete=" . escapeHtml($tripId) . "' class='driver-btn driver-btn-small driver-btn-danger' onclick=\"return confirm('Delete this ride?');\">Delete</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                  </table>
                </div>
                <?php
            }

            if ($listResult) {
                mysqli_free_result($listResult);
            }
        }
        ?>
      </div>
    </section>
  </main> 

  <nav class="bottom-nav driver-bottom-nav">
    <a href="dashboard.html"><img src="../../public-assets/icons/home.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="my-rides.php" class="active"><img src="../../public-assets/icons/car.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="earnings.html"><img src="../../public-assets/icons/dollar-sign.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="redemption.php"><img src="../../public-assets/icons/gift.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="vehicle.html"><img src="../../public-assets/icons/file-text.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
    <a href="profile.html"><img src="../../public-assets/icons/user.svg" width="24" height="24" class="icon-img" alt="" aria-hidden="true"></a>
  </nav>

  <script src="../../public-assets/script.js"></script>
</body>

</html>









