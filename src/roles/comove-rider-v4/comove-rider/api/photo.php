<?php
require_once __DIR__ . '/_bootstrap.php';

$type = isset($_GET['type']) ? strtolower(trim((string) $_GET['type'])) : '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0 || ($type !== 'rider' && $type !== 'driver')) {
    http_response_code(404);
    exit;
}

$table = $type === 'driver' ? 'DRIVER' : 'RIDER';
$idColumn = $type === 'driver' ? 'driver_id' : 'rider_id';
$row = riderFetchOne("SELECT profile_photo FROM {$table} WHERE {$idColumn} = {$id} LIMIT 1");

if (!$row || !isset($row['profile_photo']) || $row['profile_photo'] === null) {
    $fallbackPath = realpath(__DIR__ . '/../assets/avatars/default-profile.svg');
    if ($fallbackPath && is_file($fallbackPath)) {
        header('Content-Type: image/svg+xml');
        readfile($fallbackPath);
        exit;
    }
    http_response_code(404);
    exit;
}

if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_buffer($finfo, $row['profile_photo']) : false;
    if ($finfo) {
        finfo_close($finfo);
    }
    header('Content-Type: ' . ($mimeType ?: 'application/octet-stream'));
} else {
    header('Content-Type: application/octet-stream');
}
echo $row['profile_photo'];
