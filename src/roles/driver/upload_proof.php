<?php
session_start();
include "../../config/conn.php";

$request_id = $_POST['request_id'];

// STEP 1: Grab the uploaded file from the form
$file = $_FILES['proof'];

// defining folder path 
$filename = time() . '_' . $file['name']; 
$save_path = 'uploads/' . $filename; 

// picture goes into the uploads folder 
if (move_uploaded_file($file['tmp_name'], $save_path)) {
    

    $sql = "UPDATE ride_request SET proof_of_payment = ? WHERE request_id = ?";
    $stmt = mysqli_prepare($dbConn, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $save_path, $request_id);
    mysqli_stmt_execute($stmt);

    echo "success"; 
} else {
    echo "error";
}
?>