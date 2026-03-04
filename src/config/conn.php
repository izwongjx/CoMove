<?php
declare(strict_types=1);

function getDbConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'comove';

    $connection = mysqli_connect($host, $username, $password, $database);

    if ($connection === false) {
        http_response_code(500);
        exit('Database connection failed.');
    }

    mysqli_set_charset($connection, 'utf8mb4');
    return $connection;
}

// Backward compatibility with existing includes expecting $dbConn.
$dbConn = getDbConnection();

