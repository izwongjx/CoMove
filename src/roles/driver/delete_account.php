<?php
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    header('Location: ../../../index.php');
    exit();
}

$driver_id = $_SESSION['user_id'];

$sql_delete = "DELETE FROM driver WHERE driver_id = ?";
$stmt_delete = mysqli_prepare($dbConn, $sql_delete);

mysqli_stmt_bind_param($stmt_delete, 'i', $driver_id);
mysqli_stmt_execute($stmt_delete);

session_destroy();

header('Location: ../../../index.php');
exit();
?>