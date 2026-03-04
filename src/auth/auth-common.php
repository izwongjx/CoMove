<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conn.php';

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

function readRequestPayload(): array
{
    $contentType = isset($_SERVER['CONTENT_TYPE']) ? strtolower((string) $_SERVER['CONTENT_TYPE']) : '';
    if (strpos($contentType, 'application/json') !== false) {
        $rawBody = file_get_contents('php://input');
        if ($rawBody !== false && $rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
    }

    return $_POST;
}

function isApuEmail(string $email): bool
{
    return (bool) preg_match('/^[A-Za-z0-9._%+-]+@mail\.apu\.edu\.my$/i', $email);
}

function generateNextId(mysqli $dbConn, string $table, string $column, string $prefix, int $digits): string
{
    $safeTable = preg_replace('/[^A-Za-z0-9_]/', '', $table);
    $safeColumn = preg_replace('/[^A-Za-z0-9_]/', '', $column);
    $prefixLength = strlen($prefix) + 1;

    $query = sprintf(
        "SELECT %s FROM %s WHERE %s LIKE ? ORDER BY CAST(SUBSTRING(TRIM(%s), %d) AS UNSIGNED) DESC LIMIT 1",
        $safeColumn,
        $safeTable,
        $safeColumn,
        $safeColumn,
        $prefixLength
    );

    $stmt = mysqli_prepare($dbConn, $query);
    if ($stmt === false) {
        throw new RuntimeException('Failed to generate ID.');
    }

    $prefixLike = $prefix . '%';
    mysqli_stmt_bind_param($stmt, 's', $prefixLike);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $lastNumber = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        if ($row && isset($row[$column])) {
            $value = trim((string) $row[$column]);
            $pattern = '/^' . preg_quote($prefix, '/') . '(\d+)$/';
            if (preg_match($pattern, $value, $matches)) {
                $lastNumber = (int) $matches[1];
            }
        }
        mysqli_free_result($result);
    }

    mysqli_stmt_close($stmt);
    return $prefix . str_pad((string) ($lastNumber + 1), $digits, '0', STR_PAD_LEFT);
}
