<?php

namespace App\Config;

class Config
{
    private static ?Config $instance = null;
    private array $settings = [];

    private function __construct()
    {
        // Default configurations
        $this->settings = [
            'db' => [
                'host' => '127.0.0.1',
                'dbname' => 'elze_db',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ],
            'app' => [
                'name' => 'Elze.eg',
                'url' => 'http://localhost:8080/Elze.eg/public',
                'base_path' => dirname(dirname(__DIR__)),
                'env' => 'development'
            ],
            'mail' => [
                'log_path' => dirname(dirname(__DIR__)) . '/storage/logs/mail.log'
            ]
        ];

        // Load custom settings if config.local.php exists
        $localConfig = dirname(__DIR__) . '/Config/config.local.php';
        if (file_exists($localConfig)) {
            $localSettings = include $localConfig;
            if (is_array($localSettings)) {
                $this->settings = array_replace_recursive($this->settings, $localSettings);
            }
        }
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retrieve a configuration value using dot notation (e.g. 'db.host')
     */
    public function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $current = $this->settings;

        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part])) {
                $current = $current[$part];
            } else {
                return $default;
            }
        }

        return $current;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
}
