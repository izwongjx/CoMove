<?php
session_start();

include "../../config/conn.php";

$email = trim((string) ($_POST['user'] ?? ''));
$password = (string) ($_POST['pass'] ?? '');
$role = strtolower(trim(isset($_POST['role']) ? (string) $_POST['role'] : ''));

if ($email === '' || $password === '' || ($role !== 'rider' && $role !== 'driver')) {
    echo "<script>alert('Invalid login request! Please try again.');";
    die("window.history.go(-1);</script>");
}

$tableName = $role === 'driver' ? 'DRIVER' : 'RIDER';
$idColumn = $role === 'driver' ? 'driver_id' : 'rider_id';
$dashboardPath = $role === 'driver'
    ? '../../roles/driver/dashboard.php'
    : '../../roles/comove-rider-v4/comove-rider/dashboard.php';
$statusColumn = $role === 'driver' ? 'driver_status' : 'rider_status';

// Rider and driver login stays separate so admin status changes immediately affect access.
$emailSafe = mysqli_real_escape_string($dbConn, $email);
$passwordSafe = mysqli_real_escape_string($dbConn, $password);
$sql = "SELECT * FROM " . $tableName . " WHERE email = '" . $emailSafe . "' AND password = '" . $passwordSafe . "' LIMIT 1";
$result = mysqli_query($dbConn, $sql);

if (!$result || mysqli_num_rows($result) <= 0) {
    echo "<script>alert('Wrong email / password !Please Try Again!');";
    die("window.history.go(-1);</script>");
}

if ($row = mysqli_fetch_array($result)) {
    $status = strtolower(trim((string) ($row[$statusColumn] ?? 'active')));
    if ($status !== 'active') {
        mysqli_free_result($result);
        mysqli_stmt_close($stmt);
        session_unset();
        session_destroy();
        echo "<script>alert('This account is currently banned. Please contact an admin.');";
        die("window.history.go(-1);</script>");
    }

    $_SESSION['user'] = isset($row['name']) ? (string) $row['name'] : '';
    $_SESSION['email'] = isset($row['email']) ? (string) $row['email'] : '';
    $_SESSION['password'] = isset($row['password']) ? (string) $row['password'] : '';
    $_SESSION['role'] = $role;
    $_SESSION['user_id'] = isset($row[$idColumn]) ? (string) $row[$idColumn] : '';
}

mysqli_free_result($result);

$safeName = isset($_SESSION['user']) ? str_replace("'", "\\'", (string) $_SESSION['user']) : 'User';
echo "<script>alert('Welcome back! " . $safeName . "');";
echo "window.location.href='" . $dashboardPath . "';</script>";
?>
