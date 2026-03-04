<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/auth-common.php';
require_once __DIR__ . '/../config/otp-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, [
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
}

function failValidation(string $message): void
{
    respondJson(400, [
        'success' => false,
        'message' => $message
    ]);
}

function ensureEmailNotExists(mysqli $dbConn, string $email): void
{
    $query = 'SELECT email FROM RIDER WHERE email = ? UNION SELECT email FROM DRIVER WHERE email = ? LIMIT 1';
    $stmt = mysqli_prepare($dbConn, $query);
    if ($stmt === false) {
        throw new RuntimeException('Failed to prepare email lookup statement.');
    }

    mysqli_stmt_bind_param($stmt, 'ss', $email, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $exists = $result && mysqli_fetch_assoc($result);
    if ($result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);

    if ($exists) {
        failValidation('Email is already registered.');
    }
}

function readUploadedFileAsBase64(string $fieldName, bool $required): ?string
{
    if (!isset($_FILES[$fieldName])) {
        if ($required) {
            failValidation($fieldName . ' is required.');
        }
        return null;
    }

    $file = $_FILES[$fieldName];
    $error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            failValidation($fieldName . ' is required.');
        }
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        failValidation('Failed to upload file: ' . $fieldName);
    }

    $size = isset($file['size']) ? (int) $file['size'] : 0;
    if ($size > 2 * 1024 * 1024) {
        failValidation($fieldName . ' exceeds 2MB limit.');
    }

    $tmpPath = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
    if ($tmpPath === '') {
        failValidation('Invalid file upload for ' . $fieldName . '.');
    }

    $content = file_get_contents($tmpPath);
    if ($content === false) {
        failValidation('Unable to read uploaded file: ' . $fieldName);
    }

    return base64_encode($content);
}

$role = isset($_POST['role']) ? strtolower(trim((string) $_POST['role'])) : '';
if (!in_array($role, ['rider', 'driver'], true)) {
    failValidation('Invalid role selected.');
}

$email = isset($_POST['email']) ? strtolower(trim((string) $_POST['email'])) : '';
$password = isset($_POST['password']) ? (string) $_POST['password'] : '';
$confirmPassword = isset($_POST['confirmPassword']) ? (string) $_POST['confirmPassword'] : '';

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !isApuEmail($email)) {
    failValidation('Use a valid APU email in the format xxx@mail.apu.edu.my.');
}

if ($password === '' || $confirmPassword === '') {
    failValidation('Password and confirm password are required.');
}

if ($password !== $confirmPassword) {
    failValidation('Password and confirm password do not match.');
}

if (strlen($password) < 8) {
    failValidation('Password must be at least 8 characters.');
}

$dbConn = getDbConnection();
ensureEmailNotExists($dbConn, $email);

$pendingData = [
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_DEFAULT)
];

if ($role === 'rider') {
    $fullName = isset($_POST['fullName']) ? trim((string) $_POST['fullName']) : '';
    $phone = isset($_POST['phone']) ? trim((string) $_POST['phone']) : '';

    if ($fullName === '' || $phone === '') {
        failValidation('Full name and phone number are required.');
    }

    $pendingData['name'] = $fullName;
    $pendingData['phone_number'] = $phone;
} else {
    $name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
    $phoneNumber = isset($_POST['phone_number']) ? trim((string) $_POST['phone_number']) : '';
    $nricNumber = isset($_POST['nric_number']) ? strtoupper(trim((string) $_POST['nric_number'])) : '';
    $licenseExpiryDate = isset($_POST['lisence_expiry_date']) ? trim((string) $_POST['lisence_expiry_date']) : '';
    $vehicleModel = isset($_POST['vehicle_type']) ? trim((string) $_POST['vehicle_type']) : '';
    $plateNumber = isset($_POST['plate_number']) ? strtoupper(trim((string) $_POST['plate_number'])) : '';
    $color = isset($_POST['color']) ? trim((string) $_POST['color']) : '';

    if ($name === '' || $phoneNumber === '' || $nricNumber === '' || $licenseExpiryDate === '' || $plateNumber === '') {
        failValidation('Please complete all required driver details.');
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $licenseExpiryDate)) {
        failValidation('Invalid license expiry date format.');
    }

    $duplicateDriverStmt = mysqli_prepare(
        $dbConn,
        'SELECT driver_id FROM DRIVER WHERE nric_number = ? OR plate_number = ? LIMIT 1'
    );
    if ($duplicateDriverStmt === false) {
        throw new RuntimeException('Failed to prepare driver duplicate check statement.');
    }

    mysqli_stmt_bind_param($duplicateDriverStmt, 'ss', $nricNumber, $plateNumber);
    mysqli_stmt_execute($duplicateDriverStmt);
    $duplicateResult = mysqli_stmt_get_result($duplicateDriverStmt);
    $duplicateExists = $duplicateResult && mysqli_fetch_assoc($duplicateResult);
    if ($duplicateResult) {
        mysqli_free_result($duplicateResult);
    }
    mysqli_stmt_close($duplicateDriverStmt);

    if ($duplicateExists) {
        failValidation('NRIC number or plate number is already registered.');
    }

    $pendingData['name'] = $name;
    $pendingData['phone_number'] = $phoneNumber;
    $pendingData['nric_number'] = $nricNumber;
    $pendingData['lisence_expiry_date'] = $licenseExpiryDate;
    $pendingData['vehicle_model'] = $vehicleModel !== '' ? $vehicleModel : null;
    $pendingData['plate_number'] = $plateNumber;
    $pendingData['color'] = $color !== '' ? $color : null;
    $pendingData['profile_photo'] = readUploadedFileAsBase64('profile_photo', false);
    $pendingData['nric_front_image'] = readUploadedFileAsBase64('nric_front_image', true);
    $pendingData['nric_back_image'] = readUploadedFileAsBase64('nric_back_image', true);
    $pendingData['lisence_front_image'] = readUploadedFileAsBase64('lisence_front_image', true);
    $pendingData['lisence_back_image'] = readUploadedFileAsBase64('lisence_back_image', true);
}

$_SESSION['pending_registration'] = [
    'role' => $role,
    'email' => $email,
    'created_at' => time(),
    'data' => $pendingData
];

try {
    issueOtpForEmail($dbConn, $email);

    respondJson(200, [
        'success' => true,
        'message' => 'OTP sent successfully.',
        'email' => $email,
        'role' => $role
    ]);
} catch (Throwable $e) {
    unset($_SESSION['pending_registration']);
    respondJson(500, [
        'success' => false,
        'message' => 'Unable to send OTP. Please try again.'
    ]);
}
