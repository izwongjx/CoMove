<?php
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.php');
    exit();
}

$driver_id = $_SESSION['user_id'];

// ---- STEP 1: Delete ride_requests linked to this driver's trips ----
// ride_request references trip, so it must go before trip
$sql1 = "DELETE rr FROM ride_request rr 
         JOIN trip t ON rr.trip_id = t.trip_id 
         WHERE t.driver_id = ?";
$stmt1 = mysqli_prepare($dbConn, $sql1);
mysqli_stmt_bind_param($stmt1, 'i', $driver_id);
mysqli_stmt_execute($stmt1);

// ---- STEP 2: Delete the driver's trips ----
// trip references driver, so it must go before driver
$sql2 = "DELETE FROM trip WHERE driver_id = ?";
$stmt2 = mysqli_prepare($dbConn, $sql2);
mysqli_stmt_bind_param($stmt2, 'i', $driver_id);
mysqli_stmt_execute($stmt2);

// ---- STEP 3: Delete the driver's green point logs ----
// driver_green_point_log references driver
$sql3 = "DELETE FROM driver_green_point_log WHERE driver_id = ?";
$stmt3 = mysqli_prepare($dbConn, $sql3);
mysqli_stmt_bind_param($stmt3, 'i', $driver_id);
mysqli_stmt_execute($stmt3);

// ---- STEP 4: Delete the driver's redemptions ----
// driver_redemption references driver
$sql4 = "DELETE FROM driver_redemption WHERE driver_id = ?";
$stmt4 = mysqli_prepare($dbConn, $sql4);
mysqli_stmt_bind_param($stmt4, 'i', $driver_id);
mysqli_stmt_execute($stmt4);

// ---- STEP 5: Now safe to delete the driver ----
// all child records are gone, so the foreign key constraint won't block this
$sql5 = "DELETE FROM driver WHERE driver_id = ?";
$stmt5 = mysqli_prepare($dbConn, $sql5);
mysqli_stmt_bind_param($stmt5, 'i', $driver_id);
mysqli_stmt_execute($stmt5);

// wipe the session and redirect
session_destroy();
header('Location: ../../../index.php');
exit();
?>