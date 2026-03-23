<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../../config/conn.php';

if (!isset($dbConn) || !$dbConn) {
    http_response_code(500);
    exit('Database connection failed.');
}

mysqli_set_charset($dbConn, 'utf8mb4');

function adminRequireAccess(): void
{
    // Admin-only guard for the dynamic admin module.
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: ../../../index.html');
        exit;
    }
}

function adminEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function adminFetchAll(mysqli $dbConn, string $sql, string $types = '', array $params = []): array
{
    $stmt = mysqli_prepare($dbConn, $sql);
    if (!$stmt) {
        return [];
    }

    if ($types !== '' && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        mysqli_stmt_close($stmt);
        return [];
    }

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }

    mysqli_free_result($result);
    mysqli_stmt_close($stmt);
    return $rows;
}

function adminFetchOne(mysqli $dbConn, string $sql, string $types = '', array $params = []): ?array
{
    $rows = adminFetchAll($dbConn, $sql, $types, $params);
    return isset($rows[0]) ? $rows[0] : null;
}

function adminExecuteStatement(mysqli $dbConn, string $sql, string $types = '', array $params = []): bool
{
    $stmt = mysqli_prepare($dbConn, $sql);
    if (!$stmt) {
        return false;
    }

    if ($types !== '' && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $success;
}

function adminAvatarSrc(?string $blob): string
{
    if ($blob !== null && $blob !== '') {
        return 'data:image/jpeg;base64,' . base64_encode($blob);
    }

    return '../../public-assets/images/profile-icon.png';
}

function adminStatusBadgeClass(string $status): string
{
    $normalized = strtolower($status);
    if ($normalized === 'active') {
        return 'b-lime';
    }
    if ($normalized === 'pending') {
        return 'b-yellow';
    }
    if ($normalized === 'banned' || $normalized === 'rejected') {
        return 'b-red';
    }
    return 'b-gray';
}

function adminRoleBadgeClass(string $role): string
{
    return strtolower($role) === 'driver' ? 'b-purple' : 'b-blue';
}

function adminLoadAssetBlob(string $relativePath): ?string
{
    $assetPath = realpath(__DIR__ . '/' . $relativePath);
    if ($assetPath === false || !is_file($assetPath)) {
        return null;
    }

    $content = file_get_contents($assetPath);
    return $content === false ? null : $content;
}

function adminCurrentAdminId(): ?int
{
    $userId = $_SESSION['user_id'] ?? null;
    return is_numeric($userId) ? (int) $userId : null;
}
