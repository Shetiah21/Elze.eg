<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoloading bootstrap
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

use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Payment\PaymentGatewayFactory;
use App\Core\EventDispatcher;
use App\Events\OrderPlacedEvent;

echo "==========================================\n";
echo "ELZE.EG CHECKOUT & ORDERS PATTERN TEST SUITE\n";
echo "==========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // 1. Setup Test User
    $db->exec("DELETE FROM users WHERE email = 'patternuser@elze.eg'");
    $db->exec("INSERT INTO users (name, email, password, role, status, email_verified_at) VALUES ('Pattern Test User', 'patternuser@elze.eg', 'pass', 'user', 'active', NOW())");
    $userId = (int)$db->lastInsertId();
    echo "[SETUP] Created test user: Pattern Test User (ID: {$userId})\n";

    // Setup Test Address
    $db->exec("DELETE FROM addresses WHERE user_id = {$userId}");
    $db->exec("INSERT INTO addresses (user_id, recipient_name, phone_number, governorate, city, street_address, is_default) VALUES ({$userId}, 'Test Recipient', '01001234567', 'Cairo', 'Maadi', 'Road 9', 1)");
    $addressId = (int)$db->lastInsertId();
    echo "[SETUP] Created shipping address ID: {$addressId}\n";

    // Clean any old orders for safety
    $db->exec("DELETE FROM orders WHERE user_id = {$userId}");

    // Fetch test variant and verify stock
    $stmt = $db->query("SELECT id, stock FROM product_variants WHERE stock > 10 LIMIT 1");
    $variant = $stmt->fetch();
    if (!$variant) {
        throw new Exception("Test data issue: need at least 1 variant with stock > 10.");
    }
    
    $variantId = (int)$variant['id'];
    $initialStock = (int)$variant['stock'];
    echo "[SETUP] Target Variant ID: {$variantId}, Initial Stock: {$initialStock}\n\n";

    // --- TEST 1: Unique Order Number Generation ---
    echo "--- Test 1: Order Number Generation ---\n";
    $orderNum1 = 'ELZE-2026-' . strtoupper(bin2hex(random_bytes(3)));
    $orderNum2 = 'ELZE-2026-' . strtoupper(bin2hex(random_bytes(3)));
    if ($orderNum1 === $orderNum2) {
        throw new Exception("Duplicate order numbers generated.");
    }
    echo "[SUCCESS] Unique order numbers compiled correctly: {$orderNum1} and {$orderNum2}\n\n";

    // --- TEST 2: Payment Strategy & Factory Pattern ---
    echo "--- Test 2: Strategy and Factory Pattern ---\n";
    
    $order = new Order();
    $order->user_id = $userId;
    $order->order_number = $orderNum1;
    $order->subtotal = 350.00;
    $order->shipping_fee = 50.00;
    $order->discount_amount = 0.00;
    $order->tax_amount = 49.00;
    $order->total_amount = 400.00;
    $order->shipping_address_id = $addressId;
    $order->payment_method = 'cod';
    $order->payment_status = 'pending';
    $order->status = 'pending';
    $order->save();
    
    echo "Simulating COD Payment choice via Factory...\n";
    $strategyCod = PaymentGatewayFactory::create('cod');
    if (get_class($strategyCod) !== 'App\Services\Payment\CodPaymentStrategy') {
        throw new Exception("Factory did not return CodPaymentStrategy.");
    }
    
    $strategyCod->pay($order);
    if ($order->payment_status !== 'pending' || $order->status !== 'pending') {
        throw new Exception("CodPaymentStrategy failed to update status to pending.");
    }
    echo "[SUCCESS] CodPaymentStrategy ran successfully.\n";

    echo "Simulating InstaPay Payment choice (without ref code)...\n";
    $strategyInsta = PaymentGatewayFactory::create('instapay');
    if (get_class($strategyInsta) !== 'App\Services\Payment\InstapayPaymentStrategy') {
        throw new Exception("Factory did not return InstapayPaymentStrategy.");
    }
    $strategyInsta->pay($order); // Should remain pending awaiting ref code
    if ($order->payment_status !== 'pending') {
        throw new Exception("InstapayPaymentStrategy set payment status improperly without ref code.");
    }
    
    echo "Simulating InstaPay reference submission (Ref: '283749301')...\n";
    $strategyInsta->pay($order, ['reference_code' => '283749301']);
    // BUG FIX: Should now be 'pending_verification', NOT 'paid' immediately
    if ($order->payment_reference !== '283749301'
        || $order->payment_status !== 'pending_verification'
        || $order->status !== 'pending') {
        throw new Exception(
            "InstapayPaymentStrategy: expected payment_status='pending_verification' and status='pending'. "
            . "Got payment_status='{$order->payment_status}' status='{$order->status}'"
        );
    }
    echo "[SUCCESS] InstapayPaymentStrategy sets pending_verification correctly (admin review required).\n\n";

    // --- TEST 3: Observer Pattern (Event Dispatcher) ---
    echo "--- Test 3: Observer Event Dispatcher ---\n";
    
    // Register listeners on dispatcher
    $dispatcher = EventDispatcher::getInstance();
    
    // Check if observers exist
    $invObserver = new \App\Observers\InventoryObserver();
    $notObserver = new \App\Observers\NotificationObserver();
    
    $dispatcher->addListener(OrderPlacedEvent::class, [$invObserver, 'handle']);
    $dispatcher->addListener(OrderPlacedEvent::class, [$notObserver, 'handle']);

    // Setup order items line to decrement stock
    $item1 = new OrderItem();
    $item1->order_id = $order->id;
    $item1->product_id = 1;
    $item1->variant_id = $variantId;
    $item1->quantity = 3;
    $item1->unit_price = 350.00;
    $item1->total_price = 1050.00;
    $item1->save();

    // Trigger OrderPlacedEvent (inventory deferred to admin accept; notification still fires)
    echo "Dispatching OrderPlacedEvent...\n";
    $event = new OrderPlacedEvent($order, [$item1]);
    $dispatcher->dispatch($event);

    // Stock is NOT deducted on placement — verify unchanged
    $stmt = $db->prepare("SELECT stock FROM product_variants WHERE id = :id");
    $stmt->execute(['id' => $variantId]);
    $stockAfterPlace = (int)$stmt->fetch()['stock'];
    if ($stockAfterPlace !== $initialStock) {
        throw new Exception("Stock should not be deducted on order placement. Expected {$initialStock}, got {$stockAfterPlace}");
    }
    echo "[SUCCESS] Inventory deferred until admin accepts order.\n";

    // Reset to pending for accept workflow test
    $order->status = 'pending';
    $order->save();

    // Accept order via OrderManagementService to deduct stock
    $orderMgmt = new \App\Services\OrderManagementService();
    $orderMgmt->acceptOrder((int)$order->id);

    $stmt->execute(['id' => $variantId]);
    $newStock = (int)$stmt->fetch()['stock'];
    if ($newStock !== ($initialStock - 3)) {
        throw new Exception("Stock deduction on accept failed. Expected " . ($initialStock - 3) . ", got {$newStock}");
    }
    echo "[SUCCESS] OrderManagementService decremented stock on accept.\n";

    // Verify mock email was logged
    echo "Verifying email invoice receipt logged to mail.log...\n";
    $logFile = dirname(__DIR__) . '/storage/logs/mail.log';
    if (!file_exists($logFile)) {
        throw new Exception("NotificationObserver failed: mail.log not found.");
    }
    $logContent = file_get_contents($logFile);
    if (!str_contains($logContent, $orderNum1)) {
        throw new Exception("NotificationObserver failed: Invoice details not found in mail.log.");
    }
    echo "[SUCCESS] NotificationObserver logged confirmation invoice to mail.log.\n\n";

    // --- CLEANUP ---
    echo "Cleaning up test records...\n";
    $db->exec("DELETE FROM order_items WHERE order_id = {$order->id}");
    $db->exec("DELETE FROM orders WHERE user_id = {$userId}");
    $db->exec("DELETE FROM addresses WHERE user_id = {$userId}");
    $db->exec("DELETE FROM users WHERE id = {$userId}");
    
    // Restore stock
    $db->prepare("UPDATE product_variants SET stock = :st WHERE id = :id")->execute(['st' => $initialStock, 'id' => $variantId]);
    
    echo "==========================================\n";
    echo "ALL PATTERN INTEGRATION TESTS PASSED!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "[FAILURE] Integration Error: " . $e->getMessage() . "\n";
    exit(1);
}
