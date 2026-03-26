<?php
// to retreive the driverid whos logged in
session_start();

// connect to db
require_once '../../config/conn.php';

//  check if the user aka driver is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.html');
    exit();
}

// when submit the offer ride form
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $driver_id = $_SESSION['user_id'];
    $start_location = $_POST['start_location'];
    $end_location = $_POST['end_location'];
    $estimated_duration = (int)$_POST['estimated_duration'];
    $total_seats = (int)$_POST['total_seats'];
    $total_amount = (float)$_POST['total_amount'];
    $departure_time = $_POST['departure_date'] . ' ' . $_POST['departure_time'] . ':00';

    // prep the SQL ('?' are placeholders for safety against SQL injection
    $sql = "INSERT INTO trip
        (driver_id, start_location, end_location, estimated_duration, total_seats, total_amount, departure_time, trip_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')";

    // sending SQL Structure to DB
    $stmt = mysqli_prepare($dbConn, $sql);
    
    // bind the parameters into the ? 
    mysqli_stmt_bind_param($stmt, 'issiids', 
        $driver_id,
        $start_location,
        $end_location,
        $estimated_duration,
        $total_seats,
        $total_amount,
        $departure_time
    );

    // execute the statement
    if (mysqli_stmt_execute($stmt)){
        header('Location: dashboard.php?success=1');
    } else {
        header('Location: dashboard.php?error=1');
    }
}
    exit();

?>  