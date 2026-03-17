<?php
require_once __DIR__ . '/_bootstrap.php';

$pickup = isset($_GET['pickup']) ? strtolower(trim((string) $_GET['pickup'])) : '';
$drop = isset($_GET['drop']) ? strtolower(trim((string) $_GET['drop'])) : '';

$sql = "
    SELECT
        t.trip_id,
        t.start_location,
        t.end_location,
        DATE_FORMAT(t.departure_time, '%Y-%m-%d') AS departure_date,
        DATE_FORMAT(t.departure_time, '%l:%i %p') AS departure_time,
        t.total_seats,
        t.total_amount,
        t.gained_point,
        d.driver_id,
        d.name AS driver_name,
        d.vehicle_model,
        d.plate_number
    FROM TRIP t
    INNER JOIN DRIVER d ON d.driver_id = t.driver_id
    WHERE t.trip_status = 'scheduled'
";

if ($pickup !== '') {
    $pickupSql = riderEsc('%' . $pickup . '%');
    $sql .= " AND LOWER(t.start_location) LIKE '{$pickupSql}'";
}
if ($drop !== '') {
    $dropSql = riderEsc('%' . $drop . '%');
    $sql .= " AND LOWER(t.end_location) LIKE '{$dropSql}'";
}

$sql .= " ORDER BY t.departure_time ASC LIMIT 10";

$ridesRaw = riderFetchAll($sql);
$rides = [];
foreach ($ridesRaw as $ride) {
    $rides[] = [
        'trip_id' => (int) $ride['trip_id'],
        'driver_id' => (int) $ride['driver_id'],
        'driver_name' => $ride['driver_name'],
        'driver_initials' => riderInitials($ride['driver_name']),
        'driver_photo_url' => riderPhotoUrl('driver', (int) $ride['driver_id']),
        'vehicle_model' => $ride['vehicle_model'],
        'plate_number' => $ride['plate_number'],
        'from' => $ride['start_location'],
        'to' => $ride['end_location'],
        'date' => $ride['departure_date'],
        'time' => $ride['departure_time'],
        'price' => 'RM ' . number_format(((float) $ride['total_amount']) / max(1, (int) $ride['total_seats']), 2),
        'points' => (int) ($ride['gained_point'] ?? 0),
        'seats_left' => (int) $ride['total_seats'],
    ];
}

riderSuccess([
    'rides' => $rides,
]);
