<?php

namespace App\Core;

use App\Config\Config;
use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $config = Config::getInstance();
        $host = $config->get('db.host', '127.0.0.1');
        $dbname = $config->get('db.dbname', 'elze_db');
        $username = $config->get('db.username', 'root');
        $password = $config->get('db.password', '');
        $charset = $config->get('db.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
