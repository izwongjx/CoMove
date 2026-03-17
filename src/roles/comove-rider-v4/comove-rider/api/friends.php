<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();

$friendsRaw = riderFetchAll("
    SELECT
        rf.friend_id,
        r.rider_id,
        r.name,
        r.email,
        r.phone_number,
        COALESCE(points.total_points, 0) AS total_points,
        COALESCE(trips.total_trips, 0) AS total_trips
    FROM RIDER_FRIEND rf
    INNER JOIN RIDER r ON r.rider_id = rf.friend_rider_id
    LEFT JOIN (
        SELECT rider_id, COALESCE(SUM(points_change), 0) AS total_points
        FROM RIDER_GREEN_POINT_LOG
        GROUP BY rider_id
    ) points ON points.rider_id = r.rider_id
    LEFT JOIN (
        SELECT rider_id, COUNT(*) AS total_trips
        FROM RIDE_REQUEST
        WHERE request_status = 'approved'
        GROUP BY rider_id
    ) trips ON trips.rider_id = r.rider_id
    WHERE rf.rider_id = {$riderId} AND rf.status = 'accepted'
    ORDER BY r.name ASC
");

$pendingRaw = riderFetchAll("
    SELECT
        rf.friend_id,
        r.rider_id,
        r.name,
        r.email
    FROM RIDER_FRIEND rf
    INNER JOIN RIDER r ON r.rider_id = rf.friend_rider_id
    WHERE rf.rider_id = {$riderId} AND rf.status = 'pending'
    ORDER BY r.name ASC
");

$friends = [];
foreach ($friendsRaw as $friend) {
    $friends[] = [
        'friend_id' => (int) $friend['friend_id'],
        'rider_id' => (int) $friend['rider_id'],
        'name' => $friend['name'],
        'role' => 'Rider',
        'student_id' => 'RIDER-' . str_pad((string) $friend['rider_id'], 4, '0', STR_PAD_LEFT),
        'intake' => 'APU Student',
        'phone_number' => $friend['phone_number'],
        'trips_together' => (int) $friend['total_trips'],
        'green_points' => (int) $friend['total_points'],
        'photo_url' => riderPhotoUrl('rider', (int) $friend['rider_id']),
    ];
}

$pending = [];
foreach ($pendingRaw as $friend) {
    $pending[] = [
        'friend_id' => (int) $friend['friend_id'],
        'rider_id' => (int) $friend['rider_id'],
        'name' => $friend['name'],
        'meta' => $friend['email'],
        'photo_url' => riderPhotoUrl('rider', (int) $friend['rider_id']),
    ];
}

riderSuccess([
    'friends' => $friends,
    'pending' => $pending,
]);
