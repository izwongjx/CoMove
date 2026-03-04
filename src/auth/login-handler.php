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

$payload = readRequestPayload();
$role = isset($payload['role']) ? strtolower(trim((string) $payload['role'])) : '';
$email = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '';
$password = isset($payload['password']) ? (string) $payload['password'] : '';

if (!in_array($role, ['rider', 'driver'], true)) {
    respondJson(400, [
        'success' => false,
        'message' => 'Invalid role selected.'
    ]);
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !isApuEmail($email)) {
    respondJson(400, [
        'success' => false,
        'message' => 'Use a valid APU email in the format xxx@mail.apu.edu.my.'
    ]);
}

if ($password === '') {
    respondJson(400, [
        'success' => false,
        'message' => 'Password is required.'
    ]);
}

$dbConn = getDbConnection();

if ($role === 'rider') {
    $table = 'RIDER';
    $idColumn = 'rider_id';
    $statusColumn = 'rider_status';
    $redirectUrl = '../roles/rider/dashboard.html';
} else {
    $table = 'DRIVER';
    $idColumn = 'driver_id';
    $statusColumn = 'driver_status';
    $redirectUrl = '../roles/driver/dashboard.html';
}

$query = sprintf(
    'SELECT %s AS user_id, name, email, password, %s AS account_status FROM %s WHERE email = ? LIMIT 1',
    $idColumn,
    $statusColumn,
    $table
);

$stmt = mysqli_prepare($dbConn, $query);
if ($stmt === false) {
    respondJson(500, [
        'success' => false,
        'message' => 'Unable to prepare login query.'
    ]);
}

mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = $result ? mysqli_fetch_assoc($result) : null;
if ($result) {
    mysqli_free_result($result);
}
mysqli_stmt_close($stmt);

if (!$user) {
    respondJson(401, [
        'success' => false,
        'message' => 'Email or password is incorrect.'
    ]);
}

$storedPassword = (string) ($user['password'] ?? '');
$passwordMatches = password_verify($password, $storedPassword) || hash_equals($storedPassword, $password);
if (!$passwordMatches) {
    respondJson(401, [
        'success' => false,
        'message' => 'Email or password is incorrect.'
    ]);
}

$accountStatus = strtolower(trim((string) ($user['account_status'] ?? '')));
if ($role === 'rider' && $accountStatus === 'banned') {
    respondJson(403, [
        'success' => false,
        'message' => 'Your rider account is banned. Please contact support.'
    ]);
}

if ($role === 'driver' && in_array($accountStatus, ['banned', 'rejected'], true)) {
    respondJson(403, [
        'success' => false,
        'message' => 'Your driver account is not allowed to sign in.'
    ]);
}

session_regenerate_id(true);
$_SESSION['auth_user'] = [
    'id' => trim((string) $user['user_id']),
    'role' => $role,
    'name' => (string) $user['name'],
    'email' => (string) $user['email'],
    'status' => $accountStatus
];

$message = $role === 'driver' && $accountStatus === 'pending'
    ? 'Login successful. Your account is pending admin approval.'
    : 'Login successful.';

respondJson(200, [
    'success' => true,
    'message' => $message,
    'redirect_url' => $redirectUrl,
    'role' => $role
]);
