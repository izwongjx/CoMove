<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['photo']) || !is_array($_FILES['photo'])) {
        riderError('No photo uploaded.');
    }

    $file = $_FILES['photo'];
    if (!isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        riderError('Photo upload failed.');
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file((string) $file['tmp_name'])) {
        riderError('Invalid uploaded photo.');
    }

    $raw = file_get_contents((string) $file['tmp_name']);
    if ($raw === false) {
        riderError('Unable to read uploaded photo.');
    }

    $escaped = mysqli_real_escape_string($dbConn, $raw);
    mysqli_query($dbConn, "UPDATE RIDER SET profile_photo = '{$escaped}' WHERE rider_id = {$riderId} LIMIT 1");

    if (mysqli_affected_rows($dbConn) < 0) {
        riderError('Unable to update profile photo.', 500);
    }

    riderSuccess([
        'photo_url' => riderPhotoUrl('rider', $riderId),
    ]);
}

$row = riderFetchOne("
    SELECT
        rider_id,
        name,
        email,
        phone_number,
        created_at
    FROM RIDER
    WHERE rider_id = {$riderId}
    LIMIT 1
");

if (!$row) {
    riderError('Rider not found.', 404);
}

$pointsRow = riderFetchOne("SELECT COALESCE(SUM(points_change), 0) AS total_points FROM RIDER_GREEN_POINT_LOG WHERE rider_id = {$riderId}");
$tripsRow = riderFetchOne("SELECT COUNT(*) AS total_trips FROM RIDE_REQUEST WHERE rider_id = {$riderId} AND request_status = 'approved'");
$points = isset($pointsRow['total_points']) ? (int) $pointsRow['total_points'] : 0;
$trips = isset($tripsRow['total_trips']) ? (int) $tripsRow['total_trips'] : 0;

riderSuccess([
    'id' => (int) $row['rider_id'],
    'name' => $row['name'],
    'full_name' => $row['name'],
    'email' => $row['email'],
    'phone_number' => $row['phone_number'],
    'green_points' => $points,
    'total_trips' => $trips,
    'photo_url' => riderPhotoUrl('rider', $riderId),
]);
