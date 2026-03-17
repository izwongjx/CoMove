<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();

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
        d.name AS driver_name,
        d.vehicle_model,
        d.plate_number
    FROM RIDE_REQUEST rr
    INNER JOIN TRIP t ON t.trip_id = rr.trip_id
    INNER JOIN DRIVER d ON d.driver_id = t.driver_id
    WHERE rr.rider_id = {$riderId}
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
    ];
}

$history = [];
foreach ($historyRaw as $trip) {
    $history[] = [
        'request_id' => (int) $trip['request_id'],
        'driver_name' => $trip['driver_name'],
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
    'upcoming' => $upcoming,
    'history' => $history,
]);
