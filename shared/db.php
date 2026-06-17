<?php
/**
 * Shared DB helper - tất cả services dùng database 'vlxd' chung
 * (Vì chưa tách database riêng per-service trên môi trường dev)
 */
function getDbConnection($dbName = 'vatlieu') {
    $dbHost    = getenv('DB_HOST') ?: '127.0.0.1';
    $dbUser    = getenv('DB_USER') ?: 'root';
    $dbPass    = getenv('DB_PASSWORD') ?: '';
    $dbCharset = 'utf8mb4';
    $dbName    = getenv('DB_NAME') ?: $dbName;

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
    ];

    try {
        return new PDO($dsn, $dbUser, $dbPass, $pdoOptions);
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]));
    }
}
