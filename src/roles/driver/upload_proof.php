<?php
session_start();
require_once '../../config/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'driver') {
    echo 'error_auth';
    exit();
}

$request_id = (int)$_POST['request_id'];

// check if a file was actually uploaded and had no errors
if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
    echo 'error_no_file';
    exit();
}

// point to your existing uploads folder
$upload_dir = __DIR__ . '/uploads/';

// get the file extension from the original filename (e.g. "jpg", "png")
$original_name = $_FILES['proof']['name'];
$ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

// only allow image file types for security
$allowed = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $allowed)) {
    echo 'error_invalid_type';
    exit();
}

// unique filename using request_id + timestamp to avoid overwrites
$filename = 'proof_' . $request_id . '_' . time() . '.' . $ext;
$full_path = $upload_dir . $filename;

// move the temp file PHP created into your uploads folder
if (!move_uploaded_file($_FILES['proof']['tmp_name'], $full_path)) {
    echo 'error_upload_failed';
    exit();
}

// store only the relative path string into the database
$relative_path = 'uploads/' . $filename;

// update ride_request with the file path in proof_of_payment column
$sql = "UPDATE ride_request SET proof_of_payment = ? WHERE request_id = ?";
$stmt = mysqli_prepare($dbConn, $sql);
mysqli_stmt_bind_param($stmt, 'si', $relative_path, $request_id);
mysqli_stmt_execute($stmt);

// tell js it worked instead of redirecting the whole page
echo 'success';
exit();
?>