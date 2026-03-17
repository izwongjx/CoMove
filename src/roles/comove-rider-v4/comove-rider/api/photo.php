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
    http_response_code(404);
    exit;
}

header('Content-Type: image/png');
echo $row['profile_photo'];
