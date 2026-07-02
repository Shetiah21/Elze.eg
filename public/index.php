<?php

// 1. Enable Error Reporting for Development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. PSR-4 Autoloading Setup
spl_autoload_register(function ($class) {
    // Namespace prefix
    $prefix = 'App\\';
    // Base directory for prefix
    $baseDir = dirname(__DIR__) . '/app/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to next registered autoloader
        return;
    }

    // Get relative class name
    $relativeClass = substr($class, $len);

    // Replace namespace separators with directory separators, append .php
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, load it
    if (file_exists($file)) {
        require $file;
    }
});

// 3. Import Core components
use App\Core\Router;
use App\Core\Session;

// Initialize Session Singleton
$session = Session::getInstance();

// 4. Initialize Router and Register Routes
$router = new Router();

// Home page route
$router->get('/', [\App\Controllers\HomeController::class, 'index']);

// Dynamic placeholder endpoints to make sure routing works during Phase 1
$router->get('/products', function() {
    echo "<h1>Products Grid Page</h1><p>Product list details will render here in Phase 3.</p><a href='/Elze.eg/public/'>Go Home</a>";
});
$router->get('/cart', function() {
    echo "<h1>Shopping Cart</h1><p>Cart actions will render here in Phase 4.</p><a href='/Elze.eg/public/'>Go Home</a>";
});
$router->get('/login', function() {
    echo "<h1>Login Page</h1><p>Login forms will render here in Phase 2.</p><a href='/Elze.eg/public/'>Go Home</a>";
});

// 5. Dispatch Request URL
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $router->resolve($requestUri, $requestMethod);
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>Application Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
