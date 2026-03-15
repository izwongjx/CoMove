<?php
session_start();

include "../../config/conn.php";

$email = mysqli_real_escape_string($dbConn, isset($_POST['user']) ? trim((string) $_POST['user']) : '');
$password = mysqli_real_escape_string($dbConn, isset($_POST['pass']) ? (string) $_POST['pass'] : '');
$role = strtolower(trim(isset($_POST['role']) ? (string) $_POST['role'] : ''));

if ($email === '' || $password === '' || ($role !== 'rider' && $role !== 'driver')) {
    echo "<script>alert('Invalid login request! Please try again.');";
    die("window.history.go(-1);</script>");
}

$tableName = $role === 'driver' ? 'DRIVER' : 'RIDER';
$idColumn = $role === 'driver' ? 'driver_id' : 'rider_id';
$dashboardPath = $role === 'driver'
    ? '../../roles/driver/dashboard.html'
    : '../../roles/rider/dashboard.html';

// TODO: re-enable password hashing when ready (example: md5($password) or password_hash verify)
// $hashedPassword = md5($password);
$sql = "Select * from " . $tableName . " where email = '" . $email . "' and password = '" . $password . "'";
$result = mysqli_query($dbConn, $sql);

if (mysqli_num_rows($result) <= 0) {
    echo "<script>alert('Wrong email / password !Please Try Again!');";
    die("window.history.go(-1);</script>");
}

if ($row = mysqli_fetch_array($result)) {
    $_SESSION['user'] = isset($row['name']) ? (string) $row['name'] : '';
    $_SESSION['email'] = isset($row['email']) ? (string) $row['email'] : '';
    $_SESSION['password'] = isset($row['password']) ? (string) $row['password'] : '';
    $_SESSION['role'] = $role;
    $_SESSION['user_id'] = isset($row[$idColumn]) ? (string) $row[$idColumn] : '';
}

$safeName = isset($_SESSION['user']) ? str_replace("'", "\\'", (string) $_SESSION['user']) : 'User';
echo "<script>alert('Welcome back! " . $safeName . "');";
echo "window.location.href='" . $dashboardPath . "';</script>";
?>
