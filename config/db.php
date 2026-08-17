<?php
/**
 * Database connection.
 *
 * Credentials live in config/.env (gitignored — see config/.env.example
 * for the template). This file only reads them; it never hardcodes them.
 */

require_once __DIR__ . '/env.php';

$dbHost = env('DB_HOST', 'localhost');
$dbName = env('DB_NAME', 'brightframe_db');
$dbUser = env('DB_USER', 'root');
$dbPass = env('DB_PASS', '');
$dbPort = env('DB_PORT', '3306');

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    // Never leak DSN/credentials in the error shown to visitors.
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die('Sorry, something went wrong connecting to the database. Please try again later.');
}
