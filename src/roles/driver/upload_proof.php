<?php
session_start();
include "../../config/conn.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    echo "error"; exit();
}

$request_id = intval($_POST['request_id']);

if (isset($_FILES['proof']) && $_FILES['proof']['error'] === 0) {
    $original_name = $_FILES['proof']['name'];
    $tmp_path = $_FILES['proof']['tmp_name'];
    $new_filename = 'proof_' . $request_id . '_' . time() . '_' . $original_name;
    $destination = __DIR__ . '/uploads/' . $new_filename;

    if (move_uploaded_file($tmp_path, $destination)) {
        $sql = "UPDATE ride_request SET proof_of_payment = ? WHERE request_id = ?";
        $stmt = mysqli_prepare($dbConn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $new_filename, $request_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo "success"; // just return this word, no redirect
        } else {
            echo "error";
        }
    } else {
        echo "error";
    }
} else {
    echo "error";
}
?>