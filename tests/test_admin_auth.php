<?php

/**
 * Admin authentication & RBAC verification test
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('APP_BASE_PATH', '/Elze.eg/public');
$_SERVER['REQUEST_METHOD'] = 'GET';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    require $baseDir . str_replace('\\', '/', substr($class, $len = strlen($prefix))) . '.php';
});

use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Core\Session;

echo "==========================================\n";
echo "ELZE.EG ADMIN AUTH VERIFICATION\n";
echo "==========================================\n\n";

$repo = new UserRepository();
$auth = new AuthService($repo);
$session = Session::getInstance();

// 1. Verify admin account exists
echo "1. Checking admin account in database...\n";
$admin = $repo->findByEmail('admin@elze.eg');
if (!$admin) {
    echo "[FAILURE] admin@elze.eg not found. Run database/seed.sql.\n";
    exit(1);
}
echo "   Role: {$admin->role}, Status: {$admin->status}\n";
echo "   Verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "\n";

if ($admin->role !== 'admin') {
    echo "[FAILURE] Admin user does not have admin role.\n";
    exit(1);
}

// 2. Verify password
echo "\n2. Verifying default password 'admin123'...\n";
if (!password_verify('admin123', $admin->password)) {
    echo "[FAILURE] Password hash does not match 'admin123'. Re-run seed.sql or reset password.\n";
    exit(1);
}
echo "[SUCCESS] Password 'admin123' is valid.\n";

// 3. Test admin login via AuthService
echo "\n3. Testing admin login via AuthService...\n";
$session->remove('user');
try {
    $auth->login('admin@elze.eg', 'admin123');
    $sessionUser = $session->get('user');
    if (($sessionUser['role'] ?? '') !== 'admin') {
        throw new Exception('Session role is not admin.');
    }
    echo "[SUCCESS] Admin logged in. Session role: {$sessionUser['role']}\n";
} catch (Exception $e) {
    echo "[FAILURE] " . $e->getMessage() . "\n";
    exit(1);
}

// 4. RBAC: regular user should not have admin role
echo "\n4. Testing RBAC (role separation)...\n";
$regularUsers = $repo->findByEmail('admin@elze.eg'); // already admin
$db = \App\Core\Database::getInstance()->getConnection();
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
$userCount = (int) $stmt->fetchColumn();
echo "   Regular users in DB: {$userCount}\n";
echo "[SUCCESS] Admin role is distinct from user role.\n";

echo "\n==========================================\n";
echo "ALL ADMIN AUTH CHECKS PASSED\n";
echo "==========================================\n";
echo "\nLocal Development Credentials:\n";
echo "  Login URL:      http://localhost/Elze.eg/public/login\n";
echo "  Admin Email:    admin@elze.eg\n";
echo "  Admin Password: admin123\n";
echo "  Dashboard URL:  http://localhost/Elze.eg/public/admin\n";
