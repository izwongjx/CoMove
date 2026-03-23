<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? strtolower(trim((string) $_POST['action'])) : '';
    $friendId = isset($_POST['friend_id']) ? (int) $_POST['friend_id'] : 0;

    if (($action === 'accept' || $action === 'remove' || $action === 'decline') && $friendId <= 0) {
        riderError('Invalid friend request.');
    }

    if ($action === 'request') {
        $targetRiderId = isset($_POST['target_rider_id']) ? (int) $_POST['target_rider_id'] : 0;
        if ($targetRiderId <= 0 || $targetRiderId === $riderId) {
            riderError('Invalid rider selected.');
        }

        $targetRow = riderFetchOne("SELECT rider_id, name FROM RIDER WHERE rider_id = {$targetRiderId} LIMIT 1");
        if (!$targetRow) {
            riderError('Rider not found.', 404);
        }

        $existingRow = riderFetchOne("
            SELECT friend_id, rider_id, friend_rider_id, status
            FROM RIDER_FRIEND
            WHERE (rider_id = {$targetRiderId} AND friend_rider_id = {$riderId})
               OR (rider_id = {$riderId} AND friend_rider_id = {$targetRiderId})
            LIMIT 1
        ");

        if ($existingRow) {
            if ((string) $existingRow['status'] === 'rejected') {
                mysqli_query(
                    $dbConn,
                    "UPDATE RIDER_FRIEND
                     SET rider_id = {$targetRiderId}, friend_rider_id = {$riderId}, status = 'pending'
                     WHERE friend_id = " . (int) $existingRow['friend_id'] . " LIMIT 1"
                );
                riderSuccess([
                    'message' => 'Friend request sent.',
                ]);
            }

            riderError('Friend relationship already exists.');
        }

        mysqli_query($dbConn, "INSERT INTO RIDER_FRIEND (rider_id, friend_rider_id, status) VALUES ({$targetRiderId}, {$riderId}, 'pending')");
        if (mysqli_affected_rows($dbConn) <= 0) {
            riderError('Unable to send friend request.', 500);
        }

        riderSuccess([
            'message' => 'Friend request sent.',
        ]);
    }

    if ($action === 'accept') {
        $requestRow = riderFetchOne("
            SELECT friend_id, rider_id, friend_rider_id
            FROM RIDER_FRIEND
            WHERE friend_id = {$friendId} AND rider_id = {$riderId} AND status = 'pending'
            LIMIT 1
        ");

        if (!$requestRow) {
            riderError('Friend request not found.', 404);
        }

        $otherRiderId = (int) $requestRow['friend_rider_id'];
        mysqli_query($dbConn, "UPDATE RIDER_FRIEND SET status = 'accepted' WHERE friend_id = {$friendId} LIMIT 1");

        $reverseRow = riderFetchOne("
            SELECT friend_id
            FROM RIDER_FRIEND
            WHERE rider_id = {$otherRiderId} AND friend_rider_id = {$riderId}
            LIMIT 1
        ");

        if ($reverseRow) {
            mysqli_query($dbConn, "UPDATE RIDER_FRIEND SET status = 'accepted' WHERE friend_id = " . (int) $reverseRow['friend_id'] . " LIMIT 1");
        } else {
            mysqli_query($dbConn, "INSERT INTO RIDER_FRIEND (rider_id, friend_rider_id, status) VALUES ({$otherRiderId}, {$riderId}, 'accepted')");
        }

        riderSuccess([
            'message' => 'Friend request accepted.',
        ]);
    }

    if ($action === 'decline') {
        $requestRow = riderFetchOne("
            SELECT friend_id
            FROM RIDER_FRIEND
            WHERE friend_id = {$friendId} AND rider_id = {$riderId} AND status = 'pending'
            LIMIT 1
        ");

        if (!$requestRow) {
            riderError('Friend request not found.', 404);
        }

        mysqli_query($dbConn, "UPDATE RIDER_FRIEND SET status = 'rejected' WHERE friend_id = {$friendId} LIMIT 1");
        riderSuccess([
            'message' => 'Friend request declined.',
        ]);
    }

    if ($action === 'remove') {
        $friendRow = riderFetchOne("
            SELECT friend_id, rider_id, friend_rider_id
            FROM RIDER_FRIEND
            WHERE friend_id = {$friendId} AND rider_id = {$riderId}
            LIMIT 1
        ");

        if (!$friendRow) {
            riderError('Friend not found.', 404);
        }

        $otherRiderId = (int) $friendRow['friend_rider_id'];
        mysqli_query($dbConn, "DELETE FROM RIDER_FRIEND WHERE friend_id = {$friendId} LIMIT 1");
        mysqli_query($dbConn, "DELETE FROM RIDER_FRIEND WHERE rider_id = {$otherRiderId} AND friend_rider_id = {$riderId}");

        riderSuccess([
            'message' => 'Friend removed.',
        ]);
    }

    riderError('Unsupported action.');
}

$search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($search !== '') {
    $searchSql = riderEsc('%' . strtolower($search) . '%');
    $searchRaw = riderFetchAll("
        SELECT
            r.rider_id,
            r.name,
            r.email
        FROM RIDER r
        WHERE r.rider_id <> {$riderId}
          AND (
            LOWER(r.name) LIKE '{$searchSql}'
            OR LOWER(r.email) LIKE '{$searchSql}'
            OR CONCAT('RIDER-', LPAD(r.rider_id, 4, '0')) LIKE UPPER('" . riderEsc('%' . $search . '%') . "')
          )
          AND NOT EXISTS (
            SELECT 1
            FROM RIDER_FRIEND rf
            WHERE (
                rf.rider_id = r.rider_id AND rf.friend_rider_id = {$riderId}
            ) OR (
                rf.rider_id = {$riderId} AND rf.friend_rider_id = r.rider_id
            )
            AND rf.status IN ('pending', 'accepted')
          )
        ORDER BY r.name ASC
        LIMIT 10
    ");

    $results = [];
    foreach ($searchRaw as $row) {
        $results[] = [
            'rider_id' => (int) $row['rider_id'],
            'name' => $row['name'],
            'student_id' => 'RIDER-' . str_pad((string) $row['rider_id'], 4, '0', STR_PAD_LEFT),
            'meta' => $row['email'],
            'photo_url' => riderPhotoUrl('rider', (int) $row['rider_id']),
        ];
    }

    riderSuccess([
        'results' => $results,
    ]);
}

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
