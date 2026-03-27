<?php
session_start();
include "../../config/conn.php";
date_default_timezone_set('asia/kuala_lumpur');

function failBack(string $message): void
{
    $safeMessage = str_replace("'", "\\'", $message);
    echo "<script>alert('" . $safeMessage . "');";
    echo "window.history.go(-1);</script>";
    exit;
}

function readUpload(mysqli $dbConn, string $fieldName, bool $required, int $maxBytes = 614400): ?string
{
    if (!isset($_FILES[$fieldName]) || !is_array($_FILES[$fieldName])) {
        if ($required) {
            failBack('Missing required file upload.');
        }
        return null;
    }

    $file = $_FILES[$fieldName];

    if (!isset($file['error']) || (int) $file['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            failBack('Missing required file upload.');
        }
        return null;
    }

    if ((int) $file['error'] !== UPLOAD_ERR_OK) {
        failBack('File upload failed. Please try again.');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        failBack('Invalid uploaded file.');
    }

    // Get file info and compress if it's an image
    $fileInfo = @getimagesize($file['tmp_name']);
    $fileSize = filesize($file['tmp_name']);

    // Compress images larger than 200KB
    if ($fileInfo !== false && $fileSize > 200 * 1024) {
        $compressedContent = compressImage($file['tmp_name'], $fileInfo['mime']);
        if ($compressedContent !== false) {
            $rawContent = $compressedContent;
        } else {
            $rawContent = file_get_contents((string) $file['tmp_name']);
        }
    } else {
        $rawContent = file_get_contents((string) $file['tmp_name']);
    }

    if ($rawContent === false) {
        failBack('Unable to read uploaded file.');
    }

    if (strlen($rawContent) > $maxBytes) {
        $maxKb = (int) floor($maxBytes / 1024);
        failBack('Image size too big. Please upload <= ' . $maxKb . 'KB.');
    }

    return mysqli_real_escape_string($dbConn, $rawContent);
}

function compressImage($sourcePath, $mimeType, $maxWidth = 1024, $quality = 70)
{
    // Create image resource based on mime type
    switch ($mimeType) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            // Preserve alpha channel for PNG
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
            return false;
    }

    if (!$image) {
        return false;
    }

    // Get original dimensions
    $origWidth = imagesx($image);
    $origHeight = imagesy($image);

    // Calculate new dimensions
    if ($origWidth > $maxWidth) {
        $newWidth = $maxWidth;
        $newHeight = (int) floor($origHeight * ($maxWidth / $origWidth));
    } else {
        $newWidth = $origWidth;
        $newHeight = $origHeight;
    }

    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Handle transparency for PNG
    if ($mimeType === 'image/png') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

    // Compress to memory
    ob_start();
    switch ($mimeType) {
        case 'image/jpeg':
            imagejpeg($newImage, null, $quality);
            break;
        case 'image/png':
            // PNG quality: 0-9 (0 = no compression, 9 = max compression)
            $pngQuality = (int) floor((100 - $quality) / 11.11); // Convert 70% to ~6
            imagepng($newImage, null, $pngQuality);
            break;
        case 'image/gif':
            imagegif($newImage, null);
            break;
    }
    $compressedContent = ob_get_clean();

    // Clean up
    imagedestroy($image);
    imagedestroy($newImage);

    return $compressedContent;
}

function getDefaultProfilePhoto(mysqli $dbConn): ?string
{
    $defaultPath = __DIR__ . '/../../public-assets/images/profile-icon.png';
    if (!is_file($defaultPath)) {
        return null;
    }

    $rawContent = file_get_contents($defaultPath);
    if ($rawContent === false) {
        return null;
    }

    return mysqli_real_escape_string($dbConn, $rawContent);
}

function isApuEmail(string $email): bool
{
    return (bool) preg_match('/^[A-Za-z0-9._%+-]+@mail\.apu\.edu\.my$/i', $email);
}

function ensureValidOtp(mysqli $dbConn, string $email, string $otpCode): void
{
    $sql = "SELECT 1 FROM OTP WHERE email_address = '" . $email . "' AND otp_code = '" . $otpCode . "' AND is_used = FALSE AND expires_at >= NOW() LIMIT 1";
    $result = mysqli_query($dbConn, $sql);

    if (!$result || mysqli_num_rows($result) <= 0) {
        failBack('Invalid or expired OTP code. Please request a new OTP.');
    }

    mysqli_free_result($result);
}

function consumeOtp(mysqli $dbConn, string $email, string $otpCode): void
{
    mysqli_query(
        $dbConn,
        "UPDATE OTP SET is_used = TRUE WHERE email_address = '" . $email . "' AND otp_code = '" . $otpCode . "' AND is_used = FALSE AND expires_at >= NOW() ORDER BY created_at DESC LIMIT 1"
    );
}

function ensureEmailNotExists(mysqli $dbConn, string $tableName, string $email): void
{
    $sql = "SELECT 1 FROM " . $tableName . " WHERE email = '" . $email . "' LIMIT 1";
    $result = mysqli_query($dbConn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        mysqli_free_result($result);
        failBack('Email is already registered. Please login instead.');
    }

    if ($result) {
        mysqli_free_result($result);
    }
}

function ensureDriverValueNotExists(mysqli $dbConn, string $column, string $value, string $message): void
{
    if ($value === '') {
        return;
    }

    $valueSafe = mysqli_real_escape_string($dbConn, $value);
    $sql = "SELECT 1 FROM DRIVER WHERE " . $column . " = '" . $valueSafe . "' LIMIT 1";
    $result = mysqli_query($dbConn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        mysqli_free_result($result);
        failBack($message);
    }

    if ($result) {
        mysqli_free_result($result);
    }
}

$role = strtolower(trim(isset($_POST['role']) ? (string) $_POST['role'] : ''));
$password = mysqli_real_escape_string($dbConn, isset($_POST['password']) ? (string) $_POST['password'] : '');
$confirmPassword = mysqli_real_escape_string($dbConn, isset($_POST['confirmPassword']) ? (string) $_POST['confirmPassword'] : '');
$otpCode = mysqli_real_escape_string($dbConn, isset($_POST['otp_code']) ? trim((string) $_POST['otp_code']) : '');

if ($role !== 'rider' && $role !== 'driver') {
    failBack('Invalid register request.');
}

if ($password !== $confirmPassword) {
    failBack('Password and confirmed password not same!');
}

if (!preg_match('/^\d{6}$/', $otpCode)) {
    failBack('OTP code is required before registration.');
}

if ($role === 'rider') {
    $name = mysqli_real_escape_string($dbConn, isset($_POST['fullName']) ? trim((string) $_POST['fullName']) : '');
    $emailRaw = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
    $email = mysqli_real_escape_string($dbConn, strtolower($emailRaw));
    $phone = mysqli_real_escape_string($dbConn, isset($_POST['phone']) ? trim((string) $_POST['phone']) : '');
    $profilePhoto = readUpload($dbConn, 'profile_photo', false);
    if ($profilePhoto === null) {
        $profilePhoto = getDefaultProfilePhoto($dbConn);
    }

    if ($name === '' || $email === '' || $phone === '' || $password === '') {
        failBack('Please fill all required fields!');
    }

    if (!isApuEmail($email)) {
        failBack('Only APU email is allowed.');
    }

    ensureEmailNotExists($dbConn, 'RIDER', $email);
    ensureValidOtp($dbConn, $email, $otpCode);

    $profilePhotoSql = $profilePhoto === null ? "NULL" : "'" . $profilePhoto . "'";

    $createdAtSql = "NOW()";
    // TODO: re-enable password hashing when ready (example: md5($password) or password_hash)
    // $hashedPassword = md5($password);
    $sql = "Insert into RIDER (name, email, password, phone_number, profile_photo, created_at, rider_status) VALUES ('" .
        $name . "','" . $email . "','" . $password . "','" . $phone . "'," . $profilePhotoSql . "," . $createdAtSql . ",'active')";
    mysqli_query($dbConn, $sql);

    if (mysqli_affected_rows($dbConn) <= 0) {
        failBack('Unable to register! Please Try Again!');
    }
    $riderId = (int) mysqli_insert_id($dbConn);
    consumeOtp($dbConn, $email, $otpCode);

    $_SESSION['user'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = 'rider';
    $_SESSION['user_id'] = $riderId;

    echo "<script>alert('Registration completed successfully!');";
    echo "window.location.href='../../roles/comove-rider-v4/comove-rider/dashboard.php';</script>";
    exit;
}

$driverRegistration = 1;
$systemResult = mysqli_query($dbConn, 'SELECT driver_registration FROM SYSTEM_CONFIG LIMIT 1');
if ($systemResult && ($systemRow = mysqli_fetch_array($systemResult))) {
    $driverRegistration = (int) $systemRow['driver_registration'];
}
if ($systemResult) {
    mysqli_free_result($systemResult);
}

if (!$driverRegistration) {
    failBack('Driver registration is currently closed by the admin.');
}

$name = mysqli_real_escape_string($dbConn, isset($_POST['name']) ? trim((string) $_POST['name']) : '');
$emailRaw = isset($_POST['email']) ? trim((string) $_POST['email']) : '';
$email = mysqli_real_escape_string($dbConn, strtolower($emailRaw));
$phoneNumber = mysqli_real_escape_string($dbConn, isset($_POST['phone_number']) ? trim((string) $_POST['phone_number']) : '');
$nricNumber = mysqli_real_escape_string($dbConn, isset($_POST['nric_number']) ? trim((string) $_POST['nric_number']) : '');
$licenseExpiryDate = mysqli_real_escape_string($dbConn, isset($_POST['lisence_expiry_date']) ? trim((string) $_POST['lisence_expiry_date']) : '');
$vehicleModel = mysqli_real_escape_string($dbConn, isset($_POST['vehicle_type']) ? trim((string) $_POST['vehicle_type']) : '');
$plateNumber = mysqli_real_escape_string($dbConn, isset($_POST['plate_number']) ? trim((string) $_POST['plate_number']) : '');
$color = mysqli_real_escape_string($dbConn, isset($_POST['color']) ? trim((string) $_POST['color']) : '');

if (
    $name === '' ||
    $email === '' ||
    $phoneNumber === '' ||
    $password === '' ||
    $nricNumber === '' ||
    $licenseExpiryDate === '' ||
    $plateNumber === ''
) {
    failBack('Please fill all required fields!');
}

if (!isApuEmail($email)) {
    failBack('Only APU email is allowed.');
}

ensureEmailNotExists($dbConn, 'DRIVER', $email);
ensureDriverValueNotExists($dbConn, 'nric_number', $nricNumber, 'This IC/NRIC is already registered.');
ensureDriverValueNotExists($dbConn, 'plate_number', $plateNumber, 'This plate number is already registered.');
ensureValidOtp($dbConn, $email, $otpCode);

$nricFrontImage = readUpload($dbConn, 'nric_front_image', true);
$nricBackImage = readUpload($dbConn, 'nric_back_image', true);
$licenseFrontImage = readUpload($dbConn, 'lisence_front_image', true);
$licenseBackImage = readUpload($dbConn, 'lisence_back_image', true);
$profilePhoto = readUpload($dbConn, 'profile_photo', false);
if ($profilePhoto === null) {
    $profilePhoto = getDefaultProfilePhoto($dbConn);
}

$profilePhotoSql = $profilePhoto === null ? "NULL" : "'" . $profilePhoto . "'";

$createdAtSql = "NOW()";
// TODO: re-enable password hashing when ready (example: md5($password) or password_hash)
// $hashedPassword = md5($password);
$sql = "Insert into DRIVER (name, email, password, phone_number, profile_photo, created_at, driver_status, nric_number, nric_front_image, nric_back_image, license_front_image, license_back_image, license_expiry_date, vehicle_model, plate_number, color) VALUES ('" .
    $name . "','" . $email . "','" . $password . "','" . $phoneNumber . "'," . $profilePhotoSql . "," . $createdAtSql . ",'pending','" . $nricNumber . "','" . $nricFrontImage . "','" . $nricBackImage . "','" . $licenseFrontImage . "','" . $licenseBackImage . "','" . $licenseExpiryDate . "','" . $vehicleModel . "','" . $plateNumber . "','" . $color . "')";
mysqli_query($dbConn, $sql);

if (mysqli_affected_rows($dbConn) <= 0) {
    failBack('Unable to register! Please Try Again!');
}
$driverId = (int) mysqli_insert_id($dbConn);
consumeOtp($dbConn, $email, $otpCode);

$_SESSION['user'] = $name;
$_SESSION['email'] = $email;
$_SESSION['role'] = 'driver';
$_SESSION['user_id'] = $driverId;

echo "<script>alert('Registration completed successfully!');";
echo "window.location.href='../login/login.php';</script>";
?>
