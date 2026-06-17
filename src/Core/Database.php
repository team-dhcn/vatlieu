<?php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbHost    = getenv('DB_HOST') ?: '127.0.0.1';
        $dbName    = getenv('DB_NAME') ?: 'vatlieu';
        $dbUser    = getenv('DB_USER') ?: 'root';
        $dbPass    = getenv('DB_PASSWORD') ?: '';
        $dbCharset = 'utf8mb4';

        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
        ];

        try {
            $this->pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection error: ' . $e->getMessage()]));
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
