<?php
session_start();

include "../../config/conn.php";

$adminId = mysqli_real_escape_string($dbConn, isset($_POST['admin_id']) ? trim((string) $_POST['admin_id']) : '');
$password = mysqli_real_escape_string($dbConn, isset($_POST['password']) ? (string) $_POST['password'] : '');

if ($adminId === '' || $password === '') {
    echo "<script>alert('Invalid login request! Please try again.');";
    die("window.history.go(-1);</script>");
}

$sql = "Select * from ADMIN where admin_id = '" . $adminId . "' and password = '" . $password . "'";
$result = mysqli_query($dbConn, $sql);

if (!$result || mysqli_num_rows($result) <= 0) {
    echo "<script>alert('Wrong admin ID / password! Please try again.');";
    die("window.history.go(-1);</script>");
}

if ($row = mysqli_fetch_array($result)) {
    $_SESSION['user'] = isset($row['name']) ? (string) $row['name'] : '';
    $_SESSION['admin_id'] = isset($row['admin_id']) ? (string) $row['admin_id'] : '';
    $_SESSION['role'] = 'admin';
}

$safeName = isset($_SESSION['user']) ? str_replace("'", "\\'", (string) $_SESSION['user']) : 'Admin';
echo "<script>alert('Welcome back! " . $safeName . "');";
echo "window.location.href='../../roles/admin/dashboard.html';</script>";
?>
