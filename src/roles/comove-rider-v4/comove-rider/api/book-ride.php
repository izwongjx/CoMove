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
    SELECT
        t.trip_id,
        t.total_amount,
        t.total_seats,
        t.gained_point,
        GREATEST(t.total_seats - COALESCE(bookings.booked_seats, 0), 0) AS seats_left
    FROM TRIP t
    LEFT JOIN (
        SELECT trip_id, COALESCE(SUM(seats_requested), 0) AS booked_seats
        FROM RIDE_REQUEST
        WHERE request_status = 'approved'
        GROUP BY trip_id
    ) bookings ON bookings.trip_id = t.trip_id
    WHERE t.trip_id = {$tripId} AND t.trip_status = 'scheduled'
    LIMIT 1
");

if (!$trip) {
    riderError('Trip not found.', 404);
}
if ($seatsRequested > (int) $trip['seats_left']) {
    riderError('Not enough seats available for this booking.');
}

$amountPaid = ((float) $trip['total_amount']) / max(1, (int) $trip['total_seats']) * $seatsRequested;
$paymentSql = riderEsc($paymentMethod);
$insertResult = mysqli_query(
    $dbConn,
    "INSERT INTO RIDE_REQUEST (trip_id, rider_id, seats_requested, request_status, amount_paid, payment_method, gained_point)
     VALUES ({$tripId}, {$riderId}, {$seatsRequested}, 'pending', " . number_format($amountPaid, 2, '.', '') . ", '{$paymentSql}', " . (int) ($trip['gained_point'] ?? 0) . ")"
);

if (!$insertResult) {
    riderError('Unable to create booking: ' . mysqli_error($dbConn), 500);
}

if (mysqli_affected_rows($dbConn) <= 0) {
    riderError('Unable to create booking.', 500);
}

$requestId = (int) mysqli_insert_id($dbConn);

riderSuccess([
    'request_id' => $requestId,
    'status' => 'pending',
    'amount_paid' => 'RM ' . number_format($amountPaid, 2),
]);
