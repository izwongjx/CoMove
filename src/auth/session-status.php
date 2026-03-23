<?php
session_start();

include "../config/conn.php";

header('Content-Type: application/json; charset=utf-8');

function sessionStatusResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$requestedRole = strtolower(trim((string) ($_GET['role'] ?? '')));
if (!in_array($requestedRole, ['rider', 'driver'], true)) {
    sessionStatusResponse([
        'ok' => false,
        'authenticated' => false,
        'active' => false,
        'message' => 'Invalid role.',
    ], 400);
}

$sessionRole = strtolower(trim((string) ($_SESSION['role'] ?? '')));
$sessionUserId = (string) ($_SESSION['user_id'] ?? '');

if ($sessionRole !== $requestedRole || !ctype_digit($sessionUserId)) {
    sessionStatusResponse([
        'ok' => true,
        'authenticated' => false,
        'active' => false,
        'message' => 'No active session.',
    ], 401);
}

$tableName = $requestedRole === 'driver' ? 'DRIVER' : 'RIDER';
$idColumn = $requestedRole === 'driver' ? 'driver_id' : 'rider_id';
$statusColumn = $requestedRole === 'driver' ? 'driver_status' : 'rider_status';
$userId = (int) $sessionUserId;

$stmt = mysqli_prepare($dbConn, "SELECT {$statusColumn} AS account_status FROM {$tableName} WHERE {$idColumn} = ? LIMIT 1");
if (!$stmt) {
    sessionStatusResponse([
        'ok' => false,
        'authenticated' => true,
        'active' => false,
        'message' => 'Unable to verify session.',
    ], 500);
}

mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = $result ? mysqli_fetch_assoc($result) : null;

if ($result) {
    mysqli_free_result($result);
}
mysqli_stmt_close($stmt);

$status = strtolower(trim((string) ($row['account_status'] ?? '')));
if ($status !== 'active') {
    session_unset();
    session_destroy();
    sessionStatusResponse([
        'ok' => true,
        'authenticated' => false,
        'active' => false,
        'message' => 'This account is currently banned.',
    ], 403);
}

sessionStatusResponse([
    'ok' => true,
    'authenticated' => true,
    'active' => true,
    'message' => 'Session is active.',
]);
