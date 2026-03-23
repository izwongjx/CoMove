<?php
session_start();
include "../config/conn.php";
include "otp-service.php";

header('Content-Type: application/json');

function respond(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

function readPayload(): array
{
    $rawBody = file_get_contents('php://input');
    if ($rawBody !== false && $rawBody !== '') {
        $decoded = json_decode($rawBody, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }

    return $_POST;
}

function isApuEmail(string $email): bool
{
    return (bool) preg_match('/^[A-Za-z0-9._%+-]+@mail\.apu\.edu\.my$/i', $email);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, [
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
}

$payload = readPayload();
$email = isset($payload['email']) ? strtolower(trim((string) $payload['email'])) : '';

if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    respond(400, [
        'success' => false,
        'message' => 'A valid email address is required.'
    ]);
}

if (!isApuEmail($email)) {
    respond(400, [
        'success' => false,
        'message' => 'Only APU emails in the format xxx@mail.apu.edu.my are allowed.'
    ]);
}

try {
    issueOtpForEmail($dbConn, $email);

    respond(200, [
        'success' => true,
        'message' => 'OTP sent successfully.',
        'email' => $email
    ]);
} catch (Throwable $e) {
    respond(500, [
        'success' => false,
        'message' => 'Unable to generate OTP at the moment.'
    ]);
}
?>
