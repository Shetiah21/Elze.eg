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

// Dashboard Address Book Routes
$router->get('/dashboard/addresses', [\App\Controllers\DashboardController::class, 'addresses']);
$router->post('/dashboard/addresses/create', [\App\Controllers\DashboardController::class, 'createAddress']);
$router->post('/dashboard/addresses/update/{id}', [\App\Controllers\DashboardController::class, 'updateAddress']);
$router->post('/dashboard/addresses/delete/{id}', [\App\Controllers\DashboardController::class, 'deleteAddress']);
$router->post('/dashboard/addresses/make-default/{id}', [\App\Controllers\DashboardController::class, 'makeDefaultAddress']);

// Dashboard Order Routes
$router->get('/dashboard/orders', [\App\Controllers\DashboardController::class, 'orders']);
$router->get('/dashboard/orders/{id}', [\App\Controllers\DashboardController::class, 'orderDetail']);
$router->get('/orders/receipt/{id}', [\App\Controllers\DashboardController::class, 'receipt']);

// Product Catalog Routes
$router->get('/products', [\App\Controllers\ProductController::class, 'index']);
// Detail route with slug (using the router's placeholder syntax)
$router->get('/products/{slug}', [\App\Controllers\ProductController::class, 'detail']);

// Shopping Cart Routes
$router->get('/cart', [\App\Controllers\CartController::class, 'view']);
$router->post('/cart/add', [\App\Controllers\CartController::class, 'add']);
$router->post('/cart/update', [\App\Controllers\CartController::class, 'update']);
$router->post('/cart/remove', [\App\Controllers\CartController::class, 'remove']);

// Checkout Routes
$router->get('/checkout', [\App\Controllers\CheckoutController::class, 'index']);
$router->post('/checkout', [\App\Controllers\CheckoutController::class, 'process']);
$router->post('/coupon/validate', [\App\Controllers\CheckoutController::class, 'validateCoupon']);
$router->get('/checkout/instapay/{id}', [\App\Controllers\CheckoutController::class, 'instapay']);
$router->post('/checkout/instapay/verify/{id}', [\App\Controllers\CheckoutController::class, 'verifyInstapay']);

// Bootstrap Event Observers
$dispatcher = \App\Core\EventDispatcher::getInstance();
$dispatcher->addListener(\App\Events\OrderPlacedEvent::class, [new \App\Observers\InventoryObserver(), 'handle']);
$dispatcher->addListener(\App\Events\OrderPlacedEvent::class, [new \App\Observers\NotificationObserver(), 'handle']);

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
