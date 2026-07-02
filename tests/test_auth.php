<?php

// Auth flow integration test
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Models\User;
use App\Core\Database;

echo "==========================================\n";
echo "ELZE.EG AUTHENTICATION INTEGRATION TEST\n";
echo "==========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Clean up test users first
    $db->exec("DELETE FROM users WHERE email = 'testuser@elze.eg'");
    
    $userRepo = new UserRepository();
    $authService = new AuthService($userRepo);
    
    // 1. Register User
    echo "1. Registering 'testuser@elze.eg'...\n";
    $registerSuccess = $authService->register("Test Customer", "testuser@elze.eg", "password123");
    
    if (!$registerSuccess) {
        throw new Exception("Registration failed.");
    }
    echo "[SUCCESS] Registration completed.\n\n";

    // 2. Fetch code from logs/mail.log
    echo "2. Reading OTP verification code from mail.log...\n";
    $logFile = dirname(__DIR__) . '/storage/logs/mail.log';
    if (!file_exists($logFile)) {
        throw new Exception("Verification log file 'mail.log' not found.");
    }
    
    $logContent = file_get_contents($logFile);
    preg_match_all('/verify your account using the 6-digit code:\s*(\d{6})/i', $logContent, $matches);
    
    if (empty($matches[1])) {
        throw new Exception("Could not parse 6-digit code from mail.log.");
    }
    
    $otp = end($matches[1]); // Get the most recent code
    echo "[SUCCESS] Found OTP: {$otp}\n\n";
    
    // 3. Verify OTP
    echo "3. Verifying account with OTP...\n";
    $verifySuccess = $authService->verifyOtp("testuser@elze.eg", $otp);
    if (!$verifySuccess) {
        throw new Exception("Verification failed.");
    }
    echo "[SUCCESS] Account verified.\n\n";

    // 4. Try Login
    echo "4. Authenticating login credentials...\n";
    $loginSuccess = $authService->login("testuser@elze.eg", "password123", true);
    if (!$loginSuccess) {
        throw new Exception("Login authentication failed.");
    }
    echo "[SUCCESS] Login complete!\n\n";

    // 5. Clean up
    $db->exec("DELETE FROM users WHERE email = 'testuser@elze.eg'");
    echo "Test suite passed successfully!\n";

} catch (Exception $e) {
    echo "[FAILURE] Integration Error: " . $e->getMessage() . "\n";
    exit(1);
}
