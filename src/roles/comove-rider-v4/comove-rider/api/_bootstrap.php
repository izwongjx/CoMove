<?php
session_start();

require_once __DIR__ . '/../../../../config/conn.php';

if (function_exists('mysqli_set_charset')) {
    mysqli_set_charset($dbConn, 'utf8mb4');
}

function riderJsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

function riderError(string $message, int $statusCode = 400): void
{
    riderJsonResponse([
        'ok' => false,
        'message' => $message,
    ], $statusCode);
}

function riderSuccess(array $data = []): void
{
    riderJsonResponse([
        'ok' => true,
        'data' => $data,
    ]);
}

function riderCurrentId(): int
{
    if (
        isset($_SESSION['role'], $_SESSION['user_id']) &&
        $_SESSION['role'] === 'rider' &&
        ctype_digit((string) $_SESSION['user_id'])
    ) {
        return (int) $_SESSION['user_id'];
    }

    riderError('Please log in as a rider first.', 401);
}

function riderRequireActiveSession(): int
{
    global $dbConn;

    $riderId = riderCurrentId();
    $stmt = mysqli_prepare($dbConn, 'SELECT rider_status FROM RIDER WHERE rider_id = ? LIMIT 1');
    if (!$stmt) {
        riderError('Unable to verify rider access.', 500);
    }

    mysqli_stmt_bind_param($stmt, 'i', $riderId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;

    if ($result) {
        mysqli_free_result($result);
    }
    mysqli_stmt_close($stmt);

    $status = strtolower(trim((string) ($row['rider_status'] ?? '')));
    if ($status !== 'active') {
        session_unset();
        session_destroy();
        riderError('Your rider account is not active.', 403);
    }

    return $riderId;
}

function riderEsc(string $value): string
{
    global $dbConn;
    return mysqli_real_escape_string($dbConn, $value);
}

function riderFetchOne(string $sql): ?array
{
    global $dbConn;
    $result = mysqli_query($dbConn, $sql);
    if (!$result) {
        return null;
    }

    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row ?: null;
}

function riderFetchAll(string $sql): array
{
    global $dbConn;
    $result = mysqli_query($dbConn, $sql);
    if (!$result) {
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_free_result($result);
    return $rows;
}

function riderPhotoUrl(string $type, int $id): string
{
    return 'api/photo.php?type=' . rawurlencode($type) . '&id=' . $id . '&v=' . time();
}

function riderInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $initials = '';
    if (is_array($parts)) {
        foreach ($parts as $part) {
            if ($part !== '') {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }
    }
    return substr($initials, 0, 2);
}

riderRequireActiveSession();
