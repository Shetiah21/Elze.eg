<?php

// 1. Enable Error Reporting for Development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Define the application base path (the subfolder inside htdocs)
// This is the path portion that Apache serves us under, stripped before routing.
// e.g. when Apache serves http://localhost:8080/Elze.eg/public/login
//      we strip "/Elze.eg/public" so the router sees "/login"
define('APP_BASE_PATH', '/Elze.eg/public');

// 3. PSR-4 Autoloading Setup
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 4. Import Core components
use App\Core\Router;
use App\Core\Session;

// Initialize Session Singleton
$session = Session::getInstance();

// 5. Initialize Router and Register Routes
$router = new Router();

// Home page route
$router->get('/', [\App\Controllers\HomeController::class, 'index']);

// Auth Routes
$router->get('/login', [\App\Controllers\AuthController::class, 'login']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/register', [\App\Controllers\AuthController::class, 'register']);
$router->post('/register', [\App\Controllers\AuthController::class, 'register']);
$router->get('/verify-otp', [\App\Controllers\AuthController::class, 'verifyOtp']);
$router->post('/verify-otp', [\App\Controllers\AuthController::class, 'verifyOtp']);
$router->get('/resend-otp', [\App\Controllers\AuthController::class, 'resendOtp']);
$router->get('/forgot-password', [\App\Controllers\AuthController::class, 'forgotPassword']);
$router->post('/forgot-password', [\App\Controllers\AuthController::class, 'forgotPassword']);
$router->get('/reset-password', [\App\Controllers\AuthController::class, 'resetPassword']);
$router->post('/reset-password', [\App\Controllers\AuthController::class, 'resetPassword']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Dashboard Route
$router->get('/dashboard', [\App\Controllers\DashboardController::class, 'index']);

// Placeholder routes (replaced in later phases)
$router->get('/products', function() {
    echo "<h1 style='font-family:sans-serif;padding:40px'>Products Grid Page</h1><p style='padding:0 40px'>Phase 3 coming soon. <a href='" . APP_BASE_PATH . "/'>← Home</a></p>";
});
$router->get('/cart', function() {
    echo "<h1 style='font-family:sans-serif;padding:40px'>Shopping Cart</h1><p style='padding:0 40px'>Phase 4 coming soon. <a href='" . APP_BASE_PATH . "/'>← Home</a></p>";
});

// 6. Dispatch: strip APP_BASE_PATH from REQUEST_URI before resolving
$requestUri    = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Strip the base path so the router only sees the route segment
$basePath = APP_BASE_PATH;
if ($basePath !== '' && strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}
if ($requestUri === '' || $requestUri === false) {
    $requestUri = '/';
}

try {
    $router->resolve($requestUri, $requestMethod);
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
