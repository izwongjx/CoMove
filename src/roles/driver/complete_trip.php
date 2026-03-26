<?php 
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.php');
    exit();
}

// only run this when my complete buttom is being clicked

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = (int)$_POST['trip_id'];
    $driver_id = $_SESSION['user_id'];
    
    // MARK TRIP COMPLETED
    $sql_complete = "UPDATE trip SET trip_status = 'completed' WHERE trip_id = ? AND driver_id = ?";
    $stmt_complete = mysqli_prepare($dbConn, $sql_complete);
    mysqli_stmt_bind_param($stmt_complete, 'ii', $trip_id, $driver_id);
    mysqli_stmt_execute($stmt_complete);

    //  add 10 points for evrey ride 
    $points = 10;
    $source = "Trip " . $trip_id;
    $SQL_POINTS = "INSERT INTO driver_green_point_log (driver_id, points_change, source) 
                   VALUES (?, ?, ?)";

    $stmt_points = mysqli_prepare($dbConn, $SQL_POINTS);
    mysqli_stmt_bind_param($stmt_points, 'iis', $driver_id, $points, $source);
    mysqli_stmt_execute($stmt_points);

    header('Location: dashboard.php?completed=1');
    exit(); 

}
 

?>