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

function riderBuildPhotoSrc($photoBlob): string
{
    if ($photoBlob === null || $photoBlob === '') {
        return 'assets/avatars/default-profile.svg';
    }

    $mime = null;
    $header = substr($photoBlob, 0, 16);
    if (strncmp($header, "\x89PNG\r\n\x1a\n", 8) === 0) {
        $mime = 'image/png';
    } elseif (strncmp($header, "\xFF\xD8\xFF", 3) === 0) {
        $mime = 'image/jpeg';
    } elseif (strncmp($header, 'GIF87a', 6) === 0 || strncmp($header, 'GIF89a', 6) === 0) {
        $mime = 'image/gif';
    } elseif (strncmp($header, 'RIFF', 4) === 0 && substr($header, 8, 4) === 'WEBP') {
        $mime = 'image/webp';
    } elseif (substr($header, 4, 4) === 'ftyp' && (substr($header, 8, 4) === 'avif' || substr($header, 8, 4) === 'avis')) {
        $mime = 'image/avif';
    }

    if ($mime === null && class_exists('finfo')) {
        static $finfo = null;
        if ($finfo === null) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
        }
        if ($finfo) {
            $detected = $finfo->buffer($photoBlob);
            if (is_string($detected) && $detected !== '') {
                $mime = $detected;
            }
        }
    }

    if ($mime === null || $mime === '') {
        $mime = 'image/jpeg';
    }

    return 'data:' . $mime . ';base64,' . base64_encode($photoBlob);
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
