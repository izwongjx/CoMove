<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? strtolower(trim((string) $_POST['action'])) : '';
    $requestId = isset($_POST['request_id']) ? (int) $_POST['request_id'] : 0;

    if ($action === 'cancel') {
        if ($requestId <= 0) {
            riderError('Invalid trip request.');
        }

        $requestRow = riderFetchOne("
            SELECT request_id, gained_point
            FROM RIDE_REQUEST
            WHERE request_id = {$requestId} AND rider_id = {$riderId} AND request_status IN ('pending','approved')
            LIMIT 1
        ");

        if (!$requestRow) {
            riderError('Trip booking not found.', 404);
        }

        mysqli_query($dbConn, "UPDATE RIDE_REQUEST SET request_status = 'rejected' WHERE request_id = {$requestId} LIMIT 1");

        riderSuccess([
            'message' => 'Trip booking cancelled.',
        ]);
    }

    riderError('Unsupported action.');
}
$summaryTripsRow = riderFetchOne("SELECT COUNT(*) AS total_trips FROM RIDE_REQUEST WHERE rider_id = {$riderId} AND request_status <> 'rejected'");
$summaryPointsRow = riderFetchOne("SELECT COALESCE(SUM(points_change), 0) AS total_points FROM RIDER_GREEN_POINT_LOG WHERE rider_id = {$riderId}");

$upcomingRaw = riderFetchOne("
    SELECT
        rr.request_id,
        rr.amount_paid,
        rr.payment_method,
        rr.gained_point,
        rr.request_status,
        t.trip_id,
        t.start_location,
        t.end_location,
        DATE_FORMAT(t.departure_time, '%Y-%m-%d') AS ride_date,
        DATE_FORMAT(t.departure_time, '%l:%i %p') AS ride_time,
        t.estimated_duration,
        d.driver_id,
        d.name AS driver_name,
        d.vehicle_model,
        d.plate_number
    FROM RIDE_REQUEST rr
    INNER JOIN TRIP t ON t.trip_id = rr.trip_id
    INNER JOIN DRIVER d ON d.driver_id = t.driver_id
    WHERE rr.rider_id = {$riderId} AND t.trip_status = 'scheduled' AND rr.request_status IN ('pending','approved')
    ORDER BY t.departure_time ASC
    LIMIT 1
");

$historyRaw = riderFetchAll("
    SELECT
        rr.request_id,
        rr.amount_paid,
        rr.payment_method,
        rr.gained_point,
        t.estimated_duration,
        t.start_location,
        t.end_location,
        DATE_FORMAT(t.departure_time, '%b %e · %l:%i %p') AS trip_label,
        d.driver_id,
        d.name AS driver_name,
        d.vehicle_model,
        d.plate_number
    FROM RIDE_REQUEST rr
    INNER JOIN TRIP t ON t.trip_id = rr.trip_id
    INNER JOIN DRIVER d ON d.driver_id = t.driver_id
    WHERE rr.rider_id = {$riderId} AND rr.request_status <> 'rejected'
    ORDER BY t.departure_time DESC
");

$upcoming = null;
if ($upcomingRaw) {
    $upcoming = [
        'request_id' => (int) $upcomingRaw['request_id'],
        'driver_name' => $upcomingRaw['driver_name'],
        'driver_initials' => riderInitials($upcomingRaw['driver_name']),
        'driver_photo_url' => riderPhotoUrl('driver', (int) $upcomingRaw['driver_id']),
        'vehicle_model' => $upcomingRaw['vehicle_model'],
        'plate_number' => $upcomingRaw['plate_number'],
        'from' => $upcomingRaw['start_location'],
        'to' => $upcomingRaw['end_location'],
        'date' => $upcomingRaw['ride_date'],
        'time' => $upcomingRaw['ride_time'],
        'eta' => ($upcomingRaw['estimated_duration'] ?: 15) . ' min estimate',
        'fare' => 'RM ' . number_format((float) ($upcomingRaw['amount_paid'] ?? 0), 2),
        'payment_method' => $upcomingRaw['payment_method'] ?: 'Pending',
        'points' => (int) ($upcomingRaw['gained_point'] ?? 0),
        'status' => ucfirst((string) $upcomingRaw['request_status']),
        'request_id' => (int) $upcomingRaw['request_id'],
    ];
}

$history = [];
foreach ($historyRaw as $trip) {
    $history[] = [
        'request_id' => (int) $trip['request_id'],
        'driver_name' => $trip['driver_name'],
        'driver_photo_url' => riderPhotoUrl('driver', (int) $trip['driver_id']),
        'vehicle_model' => $trip['vehicle_model'],
        'plate_number' => $trip['plate_number'],
        'from' => $trip['start_location'],
        'to' => $trip['end_location'],
        'label' => $trip['trip_label'],
        'duration' => ((int) ($trip['estimated_duration'] ?? 0)) . ' min',
        'fare' => 'RM ' . number_format((float) ($trip['amount_paid'] ?? 0), 2),
        'payment_method' => $trip['payment_method'] ?: 'Pending',
        'points' => (int) ($trip['gained_point'] ?? 0),
    ];
}

riderSuccess([
    'summary' => [
        'total_trips' => isset($summaryTripsRow['total_trips']) ? (int) $summaryTripsRow['total_trips'] : 0,
        'total_points' => isset($summaryPointsRow['total_points']) ? (int) $summaryPointsRow['total_points'] : 0,
    ],
    'upcoming' => $upcoming,
    'history' => $history,
]);
