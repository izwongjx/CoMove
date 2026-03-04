<?php
declare(strict_types=1);

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

$rawBody = file_get_contents('php://input');
$payload = [];

if ($rawBody !== false && $rawBody !== '') {
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        $payload = $decoded;
    }
}

if (!$payload) {
    $payload = $_POST;
}

$email = isset($payload['email']) ? trim((string) $payload['email']) : '';

if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'A valid email address is required.'
    ]);
    exit;
}

$otpCode = (string) random_int(100000, 999999);
$_SESSION['otp_store'][$email] = [
    'code' => $otpCode,
    'expires_at' => time() + 30
];

require_once __DIR__ . '/../../phpmailer/src/Exception.php';
require_once __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

try {
    $mail = new PHPMailer(true);

    $smtpUser = getenv('OTP_SMTP_USERNAME') ?: 'comoveapu@gmail.com';
    $smtpPass = getenv('OTP_SMTP_PASSWORD') ?: 'hcpwqwwafblsouwr';
    $smtpPort = (int) (getenv('OTP_SMTP_PORT') ?: 465);

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = str_replace(' ', '', $smtpPass);
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = $smtpPort;

    $mail->setFrom($smtpUser, 'CoMove');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your CoMove OTP Code';
    $mail->Body = '<p>Your CoMove verification code is:</p><h2 style="letter-spacing:4px;">' . $otpCode . '</h2><p>This code expires in 5 minutes.</p>';
    $mail->AltBody = 'Your CoMove verification code is ' . $otpCode . '';

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP email.'
    ]);
}
