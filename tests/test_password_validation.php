<?php

// Password validation unit test
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

use App\Controllers\AuthController;

echo "==========================================\n";
echo "ELZE.EG PASSWORD VALIDATION UNIT TEST\n";
echo "==========================================\n\n";

try {
    $authController = new AuthController();
    $reflection = new ReflectionClass(AuthController::class);
    $method = $reflection->getMethod('isPasswordStrong');
    $method->setAccessible(true);

    $testCases = [
        // [password, expectedResult, reason]
        ['password', false, 'Too short and no numbers/symbols/uppercase'],
        ['Pass1', false, 'Too short (< 8 chars)'],
        ['password123', false, 'No uppercase, no special characters'],
        ['PASSWORD123!', false, 'No lowercase letters'],
        ['Password123', false, 'No special characters'],
        ['Password!', false, 'No numbers'],
        ['Pass123!', true, 'Meets all criteria: length 8, upper, lower, digit, symbol'],
        ['SecureP@ssw0rd!', true, 'Meets all criteria'],
        ['12345678aB!', true, 'Meets all criteria'],
    ];

    $failed = 0;
    foreach ($testCases as $case) {
        list($password, $expected, $reason) = $case;
        $result = $method->invoke($authController, $password);
        if ($result === $expected) {
            echo "[PASS] Password: '{$password}' - Expected: " . ($expected ? 'true' : 'false') . " - Reason: {$reason}\n";
        } else {
            echo "[FAIL] Password: '{$password}' - Expected: " . ($expected ? 'true' : 'false') . ", Got: " . ($result ? 'true' : 'false') . " - Reason: {$reason}\n";
            $failed++;
        }
    }

    if ($failed > 0) {
        throw new Exception("{$failed} test cases failed.");
    }
    echo "\nAll password validation test cases passed successfully!\n";

} catch (Exception $e) {
    echo "\n[FAILURE] Test run error: " . $e->getMessage() . "\n";
    exit(1);
}
