<?php 
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = (int)$_POST['trip_id'];
    $driver_id = $_SESSION['user_id'];
    
    // get the multiplier from green_point_config
    $sql_multiplier = "SELECT multiplier_value FROM green_point_config LIMIT 1";
    $result_multiplier = mysqli_query($dbConn, $sql_multiplier);
    $row_multiplier = mysqli_fetch_assoc($result_multiplier);
    $multiplier = $row_multiplier['multiplier_value']; // e.g. 1, 2, etc.

    // calculate points ---
    $driver_points = 10;                        
    $rider_points = 10 * $multiplier;          

    // trip mark completed
    $sql_complete = "UPDATE trip SET trip_status = 'completed', gained_point = ? WHERE trip_id = ? AND driver_id = ?";
    $stmt_complete = mysqli_prepare($dbConn, $sql_complete);
    mysqli_stmt_bind_param($stmt_complete, 'iii', $driver_points, $trip_id, $driver_id);
    mysqli_stmt_execute($stmt_complete);

    // add point for ride_request
    $sql_req_points = "UPDATE ride_request SET gained_point = ? WHERE trip_id = ?";
    $stmt_req_points = mysqli_prepare($dbConn, $sql_req_points);
    mysqli_stmt_bind_param($stmt_req_points, 'ii', $rider_points, $trip_id);
    mysqli_stmt_execute($stmt_req_points);

    // add points for driver
    $source = "Trip " . $trip_id;
    $sql_driver_log = "INSERT INTO driver_green_point_log (driver_id, points_change, source) VALUES (?, ?, ?)";
    $stmt_driver_log = mysqli_prepare($dbConn, $sql_driver_log);
    mysqli_stmt_bind_param($stmt_driver_log, 'iis', $driver_id, $driver_points, $source);
    mysqli_stmt_execute($stmt_driver_log);

    // add points for riders
    $sql_riders = "SELECT rider_id FROM ride_request WHERE trip_id = ? AND request_status = 'accepted'";
    $stmt_riders = mysqli_prepare($dbConn, $sql_riders);
    mysqli_stmt_bind_param($stmt_riders, 'i', $trip_id);
    mysqli_stmt_execute($stmt_riders);
    $result_riders = mysqli_stmt_get_result($stmt_riders);

    $riders = [];
    while ($row = mysqli_fetch_assoc($result_riders)) {
        $riders[] = $row['rider_id'];
    }

    // insert into rider gp log
    $sql_rider_log = "INSERT INTO rider_green_point_log (rider_id, points_change, source) VALUES (?, ?, ?)";
    $stmt_rider_log = mysqli_prepare($dbConn, $sql_rider_log);

    foreach ($riders as $rider_id) {
        mysqli_stmt_bind_param($stmt_rider_log, 'iis', $rider_id, $rider_points, $source);
        mysqli_stmt_execute($stmt_rider_log);
    }

    // reject pending requests
    $sql_reject_pending = "UPDATE ride_request SET request_status = 'rejected' WHERE trip_id = ? AND request_status = 'pending'";
    $stmt_reject_pending = mysqli_prepare($dbConn, $sql_reject_pending);
    mysqli_stmt_bind_param($stmt_reject_pending, 'i', $trip_id);
    mysqli_stmt_execute($stmt_reject_pending);

    header('Location: dashboard.php?completed=1');
    exit(); 
}
?>