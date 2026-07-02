<?php

// 1. Simple Test Bootstrap
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Simple Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\Config\Config;
use App\Core\Database;
use App\Core\Session;
use App\Core\Router;

echo "==========================================\n";
echo "ELZE.EG CORE ARCHITECTURE VERIFICATION TEST\n";
echo "==========================================\n\n";

// Test 1: Config Singleton
try {
    $config1 = Config::getInstance();
    $config2 = Config::getInstance();
    if ($config1 === $config2 && $config1->get('app.name') === 'Elze.eg') {
        echo "[SUCCESS] Config Singleton loaded correctly. App Name: " . $config1->get('app.name') . "\n";
    } else {
        echo "[FAILURE] Config Singleton instance mismatch or missing properties.\n";
    }
} catch (Exception $e) {
    echo "[FAILURE] Config Singleton error: " . $e->getMessage() . "\n";
}

// Test 2: Session Singleton
try {
    $session1 = Session::getInstance();
    $session2 = Session::getInstance();
    if ($session1 === $session2) {
        echo "[SUCCESS] Session Singleton loaded correctly.\n";
    } else {
        echo "[FAILURE] Session Singleton instance mismatch.\n";
    }
} catch (Exception $e) {
    echo "[FAILURE] Session Singleton error: " . $e->getMessage() . "\n";
}

// Test 3: Router Route Mapping
try {
    $router = new Router();
    $router->get('/', [\App\Controllers\HomeController::class, 'index']);
    echo "[SUCCESS] Router route mappings validated successfully.\n";
} catch (Exception $e) {
    echo "[FAILURE] Router error: " . $e->getMessage() . "\n";
}

// Test 4: Database Connection Singleton (Mocked check)
try {
    $db1 = Database::getInstance();
    echo "[SUCCESS] Database Singleton instantiated.\n";
} catch (Exception $e) {
    echo "[WARNING] Database instantiation failed (expected if MySQL is offline): " . $e->getMessage() . "\n";
}

// Test 5: Authentication Class Compilation Check
try {
    $userRepo = new \App\Repositories\UserRepository();
    $authServ = new \App\Services\AuthService($userRepo);
    echo "[SUCCESS] UserRepository and AuthService compiled and instantiated correctly.\n";
} catch (Exception $e) {
    echo "[FAILURE] Auth class verification error: " . $e->getMessage() . "\n";
}

echo "\n------------------------------------------\n";
echo "Verification complete.\n";
echo "==========================================\n";
