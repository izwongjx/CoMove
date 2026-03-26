<?php
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header("Location: ../../login.php");
    exit();
}

// grab values from php form
$request_id = $_POST['request_id'];
$action = $_POST['action'];

if ($action === 'accept'){
    $new_status = 'approved';
} else {
    $new_status = 'rejected';
}

$sql = "UPDATE ride_request SET request_status = ? WHERE request_id = ?";
$stmt = mysqli_prepare($dbConn, $sql);
mysqli_stmt_bind_param($stmt, 'si', $new_status, $request_id);
mysqli_stmt_execute($stmt);

header("Location: dashboard.php");
exit();
?>