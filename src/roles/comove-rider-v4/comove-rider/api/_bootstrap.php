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

    return 1;
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

function riderLevelInfo(int $points): array
{
    $levels = [
        ['name' => 'Eco Starter', 'min' => 0, 'max' => 499],
        ['name' => 'Eco Explorer', 'min' => 500, 'max' => 999],
        ['name' => 'Eco Commuter', 'min' => 1000, 'max' => 1499],
        ['name' => 'Eco Champion', 'min' => 1500, 'max' => 2499],
        ['name' => 'Eco Legend', 'min' => 2500, 'max' => null],
    ];

    foreach ($levels as $index => $level) {
        $max = $level['max'];
        if ($max === null || $points <= $max) {
            $next = $levels[$index + 1] ?? null;
            return [
                'level' => $index + 1,
                'title' => $level['name'],
                'current_min' => $level['min'],
                'current_max' => $max,
                'next_title' => $next ? $next['name'] : $level['name'],
                'points_to_next' => $max === null ? 0 : max(0, $max + 1 - $points),
                'progress_percent' => $max === null ? 100 : (int) round((($points - $level['min']) / max(1, ($max + 1 - $level['min']))) * 100),
            ];
        }
    }

    return [
        'level' => 1,
        'title' => 'Eco Starter',
        'current_min' => 0,
        'current_max' => 499,
        'next_title' => 'Eco Explorer',
        'points_to_next' => 500,
        'progress_percent' => 0,
    ];
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
