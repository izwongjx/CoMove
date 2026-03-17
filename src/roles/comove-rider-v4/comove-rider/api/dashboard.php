<?php
require_once __DIR__ . '/_bootstrap.php';

$riderId = riderCurrentId();
$rider = riderFetchOne("SELECT name FROM RIDER WHERE rider_id = {$riderId} LIMIT 1");
if (!$rider) {
    riderError('Rider not found.', 404);
}

$pointsRow = riderFetchOne("SELECT COALESCE(SUM(points_change), 0) AS total_points FROM RIDER_GREEN_POINT_LOG WHERE rider_id = {$riderId}");
$tripsRow = riderFetchOne("SELECT COUNT(*) AS total_trips FROM RIDE_REQUEST WHERE rider_id = {$riderId} AND request_status = 'approved'");
$points = isset($pointsRow['total_points']) ? (int) $pointsRow['total_points'] : 0;
$totalTrips = isset($tripsRow['total_trips']) ? (int) $tripsRow['total_trips'] : 0;

$availableRidesRaw = riderFetchAll("
    SELECT
        t.trip_id,
        t.start_location,
        t.end_location,
        DATE_FORMAT(t.departure_time, '%l:%i %p') AS departure_label,
        t.total_amount,
        t.gained_point,
        t.total_seats,
        GREATEST(t.total_seats - COALESCE(bookings.booked_seats, 0), 0) AS seats_left,
        d.driver_id,
        d.name AS driver_name,
        d.vehicle_model,
        d.plate_number
    FROM TRIP t
    INNER JOIN DRIVER d ON d.driver_id = t.driver_id
    LEFT JOIN (
        SELECT trip_id, COALESCE(SUM(seats_requested), 0) AS booked_seats
        FROM RIDE_REQUEST
        WHERE request_status = 'approved'
        GROUP BY trip_id
    ) bookings ON bookings.trip_id = t.trip_id
    WHERE t.trip_status = 'scheduled'
      AND GREATEST(t.total_seats - COALESCE(bookings.booked_seats, 0), 0) > 0
    ORDER BY t.departure_time ASC
    LIMIT 3
");

$availableRides = [];
foreach ($availableRidesRaw as $ride) {
    $availableRides[] = [
        'trip_id' => (int) $ride['trip_id'],
        'driver_id' => (int) $ride['driver_id'],
        'driver_name' => $ride['driver_name'],
        'driver_initials' => riderInitials($ride['driver_name']),
        'photo_url' => riderPhotoUrl('driver', (int) $ride['driver_id']),
        'from' => $ride['start_location'],
        'to' => $ride['end_location'],
        'departure_time' => $ride['departure_label'],
        'price' => 'RM ' . number_format(((float) $ride['total_amount']) / max(1, (int) $ride['total_seats']), 2),
        'unit_price' => (float) $ride['total_amount'] / max(1, (int) $ride['total_seats']),
        'vehicle_model' => $ride['vehicle_model'],
        'plate_number' => $ride['plate_number'],
        'points' => (int) ($ride['gained_point'] ?? 0),
        'seats_left' => (int) $ride['seats_left'],
    ];
}

$recentTripsRaw = riderFetchAll("
    SELECT
        rr.request_id,
        rr.gained_point,
        t.start_location,
        t.end_location,
        DATE_FORMAT(t.departure_time, '%b %e · %l:%i %p') AS departure_label,
        d.name AS driver_name,
        d.plate_number
    FROM RIDE_REQUEST rr
    INNER JOIN TRIP t ON t.trip_id = rr.trip_id
    INNER JOIN DRIVER d ON d.driver_id = t.driver_id
    WHERE rr.rider_id = {$riderId} AND rr.request_status = 'approved'
    ORDER BY t.departure_time DESC
    LIMIT 3
");

$recentTrips = [];
foreach ($recentTripsRaw as $trip) {
    $recentTrips[] = [
        'request_id' => (int) $trip['request_id'],
        'route' => $trip['start_location'] . ' → ' . $trip['end_location'],
        'meta' => $trip['departure_label'] . ' · ' . $trip['driver_name'] . ' · ' . $trip['plate_number'],
        'points' => (int) ($trip['gained_point'] ?? 0),
    ];
}

riderSuccess([
    'name' => $rider['name'],
    'green_points' => $points,
    'total_trips' => $totalTrips,
    'available_rides' => $availableRides,
    'recent_trips' => $recentTrips,
]);
