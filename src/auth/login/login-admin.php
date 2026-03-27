<?php
session_start();

include "../../config/conn.php";

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo "<script>alert('Invalid login request! Please try again.');";
    die("window.history.go(-1);</script>");
}

$emailSafe = mysqli_real_escape_string($dbConn, $email);
$passwordSafe = mysqli_real_escape_string($dbConn, $password);
$hashedPassword = mysqli_real_escape_string($dbConn, md5($password));
$sql = "SELECT * FROM ADMIN WHERE email = '" . $emailSafe . "' AND (password = '" . $hashedPassword . "' OR password = '" . $passwordSafe . "') LIMIT 1";
$result = mysqli_query($dbConn, $sql);

if (!$result || mysqli_num_rows($result) <= 0) {
    echo "<script>alert('Wrong email / password! Please try again.');";
    die("window.history.go(-1);</script>");
}

if ($row = mysqli_fetch_array($result)) {
    $_SESSION['user'] = isset($row['name']) ? (string) $row['name'] : '';
    $_SESSION['admin_id'] = isset($row['admin_id']) ? (string) $row['admin_id'] : '';
    $_SESSION['email'] = isset($row['email']) ? (string) $row['email'] : '';
    $_SESSION['role'] = 'admin';
}

mysqli_free_result($result);

$safeName = isset($_SESSION['user']) ? str_replace("'", "\\'", (string) $_SESSION['user']) : 'Admin';
echo "<script>alert('Welcome back! " . $safeName . "');";
echo "window.location.href='../../roles/admin/dashboard.php';</script>";
?>
