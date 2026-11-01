<?php
declare(strict_types=1);

$databaseHost = 'localhost';
$databaseName = 'unisp_end_db';
$databaseUser = 'root';
$databasePassword = '';

try {
    $pdo = new PDO(
        "mysql:host={$databaseHost};dbname={$databaseName};charset=utf8mb4",
        $databaseUser,
        $databasePassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $exception) {
    http_response_code(500);
    exit(
        'Could not connect to MySQL. Start MySQL in XAMPP and import '
        . 'database.sql using phpMyAdmin before opening UniSpend.'
    );
}
