<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/auth-common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, [
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
}

function validationError(string $message): void
{
    respondJson(400, [
        'success' => false,
        'message' => $message
    ]);
}

function decodeBase64File(?string $encodedValue, string $fieldName, bool $required): ?string
{
    if ($encodedValue === null || $encodedValue === '') {
        if ($required) {
            validationError('Missing required file data: ' . $fieldName);
        }
        return null;
    }

    $decoded = base64_decode($encodedValue, true);
    if ($decoded === false) {
        validationError('Invalid file data for: ' . $fieldName);
    }

    return $decoded;
}

$payload = readRequestPayload();
$role = isset($payload['role']) ? strtolower(trim((string) $payload['role'])) : '';
$email = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '';
$otpCode = isset($payload['otp']) ? trim((string) $payload['otp']) : '';

if (!in_array($role, ['rider', 'driver'], true)) {
    validationError('Invalid role.');
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !isApuEmail($email)) {
    validationError('Invalid email format.');
}

if (!preg_match('/^\d{6}$/', $otpCode)) {
    validationError('OTP must be a 6-digit code.');
}

$pendingRegistration = $_SESSION['pending_registration'] ?? null;
if (!is_array($pendingRegistration)) {
    validationError('No pending registration found. Please register again.');
}

$pendingRole = (string) ($pendingRegistration['role'] ?? '');
$pendingEmail = (string) ($pendingRegistration['email'] ?? '');
if ($pendingRole !== $role || strtolower($pendingEmail) !== $email) {
    validationError('Registration details do not match this OTP request.');
}

$createdAt = isset($pendingRegistration['created_at']) ? (int) $pendingRegistration['created_at'] : 0;
if ($createdAt <= 0 || (time() - $createdAt) > 900) {
    unset($_SESSION['pending_registration']);
    validationError('Registration session expired. Please register again.');
}

$pendingData = isset($pendingRegistration['data']) && is_array($pendingRegistration['data'])
    ? $pendingRegistration['data']
    : [];

$dbConn = getDbConnection();

$otpStmt = mysqli_prepare(
    $dbConn,
    'SELECT otp_id, otp_code, is_used, expires_at FROM OTP WHERE email_address = ? ORDER BY created_at DESC LIMIT 1'
);
if ($otpStmt === false) {
    respondJson(500, [
        'success' => false,
        'message' => 'Unable to verify OTP right now.'
    ]);
}

mysqli_stmt_bind_param($otpStmt, 's', $email);
mysqli_stmt_execute($otpStmt);
$otpResult = mysqli_stmt_get_result($otpStmt);
$otpRow = $otpResult ? mysqli_fetch_assoc($otpResult) : null;
if ($otpResult) {
    mysqli_free_result($otpResult);
}
mysqli_stmt_close($otpStmt);

if (!$otpRow) {
    validationError('OTP was not found. Please request a new OTP.');
}

if ((int) $otpRow['is_used'] === 1) {
    validationError('This OTP has already been used. Please request a new OTP.');
}

if (strtotime((string) $otpRow['expires_at']) < time()) {
    validationError('OTP has expired. Please request a new OTP.');
}

if (!hash_equals((string) $otpRow['otp_code'], $otpCode)) {
    validationError('Incorrect OTP code.');
}

mysqli_begin_transaction($dbConn);

try {
    $existsStmt = mysqli_prepare($dbConn, 'SELECT email FROM RIDER WHERE email = ? UNION SELECT email FROM DRIVER WHERE email = ? LIMIT 1');
    if ($existsStmt === false) {
        throw new RuntimeException('Unable to validate existing email.');
    }

    mysqli_stmt_bind_param($existsStmt, 'ss', $email, $email);
    mysqli_stmt_execute($existsStmt);
    $existsResult = mysqli_stmt_get_result($existsStmt);
    $exists = $existsResult && mysqli_fetch_assoc($existsResult);
    if ($existsResult) {
        mysqli_free_result($existsResult);
    }
    mysqli_stmt_close($existsStmt);

    if ($exists) {
        throw new RuntimeException('This email is already registered.');
    }

    if ($role === 'rider') {
        $riderId = generateNextId($dbConn, 'RIDER', 'rider_id', 'R', 7);
        $name = trim((string) ($pendingData['name'] ?? ''));
        $phoneNumber = trim((string) ($pendingData['phone_number'] ?? ''));
        $passwordHash = (string) ($pendingData['password_hash'] ?? '');

        if ($name === '' || $phoneNumber === '' || $passwordHash === '') {
            throw new RuntimeException('Rider registration data is incomplete.');
        }

        $insertRiderStmt = mysqli_prepare(
            $dbConn,
            'INSERT INTO RIDER (rider_id, name, email, password, phone_number) VALUES (?, ?, ?, ?, ?)'
        );
        if ($insertRiderStmt === false) {
            throw new RuntimeException('Unable to prepare rider registration.');
        }

        mysqli_stmt_bind_param($insertRiderStmt, 'sssss', $riderId, $name, $email, $passwordHash, $phoneNumber);
        if (!mysqli_stmt_execute($insertRiderStmt)) {
            throw new RuntimeException('Failed to create rider account.');
        }
        mysqli_stmt_close($insertRiderStmt);

        $userId = $riderId;
        $redirectUrl = '../roles/rider/dashboard.html';
        $userStatus = 'active';
    } else {
        $driverId = generateNextId($dbConn, 'DRIVER', 'driver_id', 'D', 7);
        $name = trim((string) ($pendingData['name'] ?? ''));
        $phoneNumber = trim((string) ($pendingData['phone_number'] ?? ''));
        $passwordHash = (string) ($pendingData['password_hash'] ?? '');
        $nricNumber = strtoupper(trim((string) ($pendingData['nric_number'] ?? '')));
        $licenseExpiryDate = trim((string) ($pendingData['lisence_expiry_date'] ?? ''));
        $vehicleModel = $pendingData['vehicle_model'] ?? null;
        $plateNumber = strtoupper(trim((string) ($pendingData['plate_number'] ?? '')));
        $color = $pendingData['color'] ?? null;

        if ($name === '' || $phoneNumber === '' || $passwordHash === '' || $nricNumber === '' || $licenseExpiryDate === '' || $plateNumber === '') {
            throw new RuntimeException('Driver registration data is incomplete.');
        }

        $profilePhoto = decodeBase64File(
            isset($pendingData['profile_photo']) ? (string) $pendingData['profile_photo'] : null,
            'profile_photo',
            false
        );
        $nricFrontImage = decodeBase64File((string) ($pendingData['nric_front_image'] ?? ''), 'nric_front_image', true);
        $nricBackImage = decodeBase64File((string) ($pendingData['nric_back_image'] ?? ''), 'nric_back_image', true);
        $licenseFrontImage = decodeBase64File((string) ($pendingData['lisence_front_image'] ?? ''), 'lisence_front_image', true);
        $licenseBackImage = decodeBase64File((string) ($pendingData['lisence_back_image'] ?? ''), 'lisence_back_image', true);

        $insertDriverStmt = mysqli_prepare(
            $dbConn,
            'INSERT INTO DRIVER (
                driver_id, name, email, password, phone_number, profile_photo,
                nric_number, nric_front_image, nric_back_image,
                lisence_front_image, lisence_back_image, lisence_expiry_date,
                vehicle_model, plate_number, color
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        if ($insertDriverStmt === false) {
            throw new RuntimeException('Unable to prepare driver registration.');
        }

        mysqli_stmt_bind_param(
            $insertDriverStmt,
            'sssssssssssssss',
            $driverId,
            $name,
            $email,
            $passwordHash,
            $phoneNumber,
            $profilePhoto,
            $nricNumber,
            $nricFrontImage,
            $nricBackImage,
            $licenseFrontImage,
            $licenseBackImage,
            $licenseExpiryDate,
            $vehicleModel,
            $plateNumber,
            $color
        );

        if (!mysqli_stmt_execute($insertDriverStmt)) {
            throw new RuntimeException('Failed to create driver account.');
        }
        mysqli_stmt_close($insertDriverStmt);

        $userId = $driverId;
        $redirectUrl = '../roles/driver/dashboard.html';
        $userStatus = 'pending';
    }

    $markOtpStmt = mysqli_prepare($dbConn, 'UPDATE OTP SET is_used = 1 WHERE otp_id = ?');
    if ($markOtpStmt === false) {
        throw new RuntimeException('Unable to mark OTP as used.');
    }

    $otpId = (string) $otpRow['otp_id'];
    mysqli_stmt_bind_param($markOtpStmt, 's', $otpId);
    if (!mysqli_stmt_execute($markOtpStmt)) {
        throw new RuntimeException('Failed to update OTP state.');
    }
    mysqli_stmt_close($markOtpStmt);

    mysqli_commit($dbConn);

    unset($_SESSION['pending_registration']);
    session_regenerate_id(true);
    $_SESSION['auth_user'] = [
        'id' => $userId,
        'role' => $role,
        'name' => (string) ($pendingData['name'] ?? ''),
        'email' => $email,
        'status' => $userStatus
    ];

    respondJson(200, [
        'success' => true,
        'message' => 'Registration completed successfully.',
        'user_id' => $userId,
        'role' => $role,
        'redirect_url' => $redirectUrl
    ]);
} catch (Throwable $e) {
    mysqli_rollback($dbConn);
    respondJson(500, [
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
