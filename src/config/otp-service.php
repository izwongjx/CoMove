<?php
declare(strict_types=1);

require_once __DIR__ . '/../../phpmailer/src/Exception.php';
require_once __DIR__ . '/../../phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../../phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

function generateOtpId(mysqli $dbConn): string
{
    $query = "SELECT otp_id FROM OTP WHERE otp_id LIKE 'OTP%' ORDER BY CAST(SUBSTRING(TRIM(otp_id), 4) AS UNSIGNED) DESC LIMIT 1";
    $result = mysqli_query($dbConn, $query);

    if (!$result) {
        return 'OTP00001';
    }

    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);

    $lastNumber = 0;
    if ($row && isset($row['otp_id'])) {
        $lastId = trim((string) $row['otp_id']);
        if (preg_match('/^OTP(\d+)$/', $lastId, $matches)) {
            $lastNumber = (int) $matches[1];
        }
    }

    return 'OTP' . str_pad((string) ($lastNumber + 1), 5, '0', STR_PAD_LEFT);
}

function issueOtpForEmail(mysqli $dbConn, string $email): void
{
    $otpCode = (string) random_int(100000, 999999);
    $otpId = generateOtpId($dbConn);
    $expiresAt = date('Y-m-d H:i:s', time() + 300);

    mysqli_begin_transaction($dbConn);

    try {
        $expireOldStmt = mysqli_prepare($dbConn, 'UPDATE OTP SET is_used = 1 WHERE email_address = ? AND is_used = 0');
        if ($expireOldStmt === false) {
            throw new RuntimeException('Unable to prepare OTP update statement.');
        }

        mysqli_stmt_bind_param($expireOldStmt, 's', $email);
        if (!mysqli_stmt_execute($expireOldStmt)) {
            throw new RuntimeException('Unable to invalidate previous OTP records.');
        }
        mysqli_stmt_close($expireOldStmt);

        $insertStmt = mysqli_prepare(
            $dbConn,
            'INSERT INTO OTP (otp_id, email_address, otp_code, is_used, expires_at) VALUES (?, ?, ?, 0, ?)'
        );
        if ($insertStmt === false) {
            throw new RuntimeException('Unable to prepare OTP insert statement.');
        }

        mysqli_stmt_bind_param($insertStmt, 'ssss', $otpId, $email, $otpCode, $expiresAt);
        if (!mysqli_stmt_execute($insertStmt)) {
            throw new RuntimeException('Unable to store OTP in database.');
        }
        mysqli_stmt_close($insertStmt);

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

        mysqli_commit($dbConn);
    } catch (Throwable $e) {
        mysqli_rollback($dbConn);
        throw $e;
    }
}
