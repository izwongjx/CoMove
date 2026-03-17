<?php
require_once __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    riderError('Invalid request method.', 405);
}

$riderId = riderCurrentId();
$tripId = isset($_POST['trip_id']) ? (int) $_POST['trip_id'] : 0;
$paymentMethod = isset($_POST['payment_method']) ? trim((string) $_POST['payment_method']) : '';
$seatsRequested = isset($_POST['seats_requested']) ? max(1, (int) $_POST['seats_requested']) : 1;

if ($tripId <= 0 || $paymentMethod === '') {
    riderError('Missing booking details.');
}

$trip = riderFetchOne("
    SELECT trip_id, total_amount, total_seats, gained_point
    FROM TRIP
    WHERE trip_id = {$tripId} AND trip_status = 'scheduled'
    LIMIT 1
");

if (!$trip) {
    riderError('Trip not found.', 404);
}

$amountPaid = ((float) $trip['total_amount']) / max(1, (int) $trip['total_seats']) * $seatsRequested;
$paymentSql = riderEsc($paymentMethod);
mysqli_query(
    $dbConn,
    "INSERT INTO RIDE_REQUEST (trip_id, rider_id, seats_requested, request_status, amount_paid, payment_method, gained_point)
     VALUES ({$tripId}, {$riderId}, {$seatsRequested}, 'approved', " . number_format($amountPaid, 2, '.', '') . ", '{$paymentSql}', " . (int) ($trip['gained_point'] ?? 0) . ")"
);

if (mysqli_affected_rows($dbConn) <= 0) {
    riderError('Unable to create booking.', 500);
}

mysqli_query(
    $dbConn,
    "INSERT INTO RIDER_GREEN_POINT_LOG (rider_id, points_change, source)
     VALUES ({$riderId}, " . (int) ($trip['gained_point'] ?? 0) . ", 'Trip {$tripId}')"
);

$reference = 'CMV-' . strtoupper(substr(md5((string) microtime(true) . '-' . (string) $tripId . '-' . (string) $riderId), 0, 8));

riderSuccess([
    'reference' => $reference,
    'amount_paid' => 'RM ' . number_format($amountPaid, 2),
]);
