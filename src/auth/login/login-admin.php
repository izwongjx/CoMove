<?php
session_start();

include "../../config/conn.php";

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo "<script>alert('Invalid login request! Please try again.');";
    die("window.history.go(-1);</script>");
}

$sql = "SELECT * FROM ADMIN WHERE email = ? AND password = ? LIMIT 1";
$stmt = mysqli_prepare($dbConn, $sql);

if (!$stmt) {
    echo "<script>alert('Login service is unavailable right now. Please try again.');";
    die("window.history.go(-1);</script>");
}

mysqli_stmt_bind_param($stmt, 'ss', $email, $password);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) <= 0) {
    mysqli_stmt_close($stmt);
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
mysqli_stmt_close($stmt);

$safeName = isset($_SESSION['user']) ? str_replace("'", "\\'", (string) $_SESSION['user']) : 'Admin';
echo "<script>alert('Welcome back! " . $safeName . "');";
echo "window.location.href='../../roles/admin/dashboard.php';</script>";
?>
