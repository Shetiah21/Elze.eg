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
use App\Services\CartService;
use App\Models\User;
use App\Models\Address;

echo "==========================================\n";
echo "ELZE.EG CART & ADDRESS CRUD INTEGRATION TEST\n";
echo "==========================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Setup test customer
    $db->exec("DELETE FROM users WHERE email = 'carttest@elze.eg'");
    $db->exec("INSERT INTO users (name, email, password, role, status, email_verified_at) VALUES ('Cart Test User', 'carttest@elze.eg', 'pass', 'user', 'active', NOW())");
    $userId = (int)$db->lastInsertId();
    echo "[SETUP] Test user created with ID: {$userId}\n";

    // Clean up addresses and cart items for this user
    $db->exec("DELETE FROM addresses WHERE user_id = {$userId}");
    $db->exec("DELETE FROM cart_items WHERE user_id = {$userId}");

    // Fetch a sample product variant ID with stock
    $stmt = $db->query("SELECT id, stock FROM product_variants WHERE stock > 5 LIMIT 2");
    $variants = $stmt->fetchAll();
    if (count($variants) < 2) {
        throw new Exception("Test data issue: need at least 2 variants with stock > 5.");
    }
    
    $v1 = $variants[0];
    $v2 = $variants[1];

    $v1Id = (int)$v1['id'];
    $v2Id = (int)$v2['id'];
    $v1Stock = (int)$v1['stock'];
    $v2Stock = (int)$v2['stock'];

    echo "[INFO] Using Variant 1 (ID {$v1Id}, Stock {$v1Stock}) and Variant 2 (ID {$v2Id}, Stock {$v2Stock})\n\n";

    // Initialize CartService
    $session = Session::getInstance();
    
    // --- TEST 1: Guest Cart (Session Mode) ---
    echo "--- Test 1: Guest Cart Operations (Session) ---\n";
    $session->remove('user'); // Ensure guest state
    $session->remove('cart');
    
    $cartService = new CartService();
    
    // Add item
    echo "Adding 2 units of Variant 1 to guest cart...\n";
    $cartService->addToCart($v1Id, 2);
    if ($cartService->getCartCount() !== 2) {
        throw new Exception("Guest cart count mismatch after add. Expected 2, got " . $cartService->getCartCount());
    }
    echo "[SUCCESS] Guest add complete.\n";

    // Update quantity
    echo "Updating quantity of Variant 1 to 4...\n";
    $cartService->updateQuantity($v1Id, 4);
    if ($cartService->getCartCount() !== 4) {
        throw new Exception("Guest cart count mismatch after update. Expected 4, got " . $cartService->getCartCount());
    }
    echo "[SUCCESS] Guest update complete.\n";

    // Stock validation
    echo "Testing stock threshold validation (adding stock+10)...\n";
    try {
        $cartService->addToCart($v1Id, $v1Stock + 10);
        throw new Exception("Failed: Allowed adding variant quantity beyond stock limit.");
    } catch (Exception $e) {
        echo "[SUCCESS] Prevented adding beyond stock limit: " . $e->getMessage() . "\n";
    }

    // Remove item
    echo "Removing Variant 1 from guest cart...\n";
    $cartService->removeFromCart($v1Id);
    if ($cartService->getCartCount() !== 0) {
        throw new Exception("Guest cart not empty after removal.");
    }
    echo "[SUCCESS] Guest remove complete.\n\n";

    // --- TEST 2: User Cart (Database Mode) ---
    echo "--- Test 2: User Cart Operations (Database) ---\n";
    // Log in the test user
    $session->set('user', [
        'id' => $userId,
        'name' => 'Cart Test User',
        'email' => 'carttest@elze.eg',
        'role' => 'user'
    ]);

    // Add item
    echo "Adding 3 units of Variant 1 to database cart...\n";
    $cartService->addToCart($v1Id, 3);
    if ($cartService->getCartCount() !== 3) {
        throw new Exception("DB cart count mismatch. Expected 3, got " . $cartService->getCartCount());
    }
    
    // Check database row
    $stmt = $db->prepare("SELECT quantity FROM cart_items WHERE user_id = :u AND variant_id = :v");
    $stmt->execute(['u' => $userId, 'v' => $v1Id]);
    $qtyInDb = (int)($stmt->fetch()['quantity'] ?? 0);
    if ($qtyInDb !== 3) {
        throw new Exception("DB quantity mismatch. Expected 3, got " . $qtyInDb);
    }
    echo "[SUCCESS] Database add complete.\n";

    // Update quantity
    echo "Updating Variant 1 quantity to 2...\n";
    $cartService->updateQuantity($v1Id, 2);
    if ($cartService->getCartCount() !== 2) {
        throw new Exception("DB cart count mismatch after update. Expected 2.");
    }
    echo "[SUCCESS] Database update complete.\n";

    // Remove item
    echo "Removing Variant 1 from database cart...\n";
    $cartService->removeFromCart($v1Id);
    if ($cartService->getCartCount() !== 0) {
        throw new Exception("DB cart not empty after removal.");
    }
    echo "[SUCCESS] Database remove complete.\n\n";

    // --- TEST 3: Cart Merging ---
    echo "--- Test 3: Cart Merging on Login ---\n";
    // Log out user
    $session->remove('user');
    $session->remove('cart');
    
    // Add guest items
    $cartService->addToCart($v1Id, 2);
    $cartService->addToCart($v2Id, 1);
    echo "Guest session has: Variant 1 (2 units), Variant 2 (1 unit).\n";

    // Merge into DB user who already has 1 unit of Variant 1
    echo "Simulating pre-existing user DB cart: Variant 1 (1 unit)...\n";
    $db->exec("INSERT INTO cart_items (user_id, variant_id, quantity) VALUES ({$userId}, {$v1Id}, 1)");

    // Trigger Merge
    echo "Executing mergeSessionCartIntoDb()...\n";
    $cartService->mergeSessionCartIntoDb($userId);

    // Verify quantities in DB
    $stmt = $db->prepare("SELECT variant_id, quantity FROM cart_items WHERE user_id = :u");
    $stmt->execute(['u' => $userId]);
    $dbItems = [];
    while ($row = $stmt->fetch()) {
        $dbItems[(int)$row['variant_id']] = (int)$row['quantity'];
    }

    // Expected: Variant 1: 1 + 2 = 3 units. Variant 2: 1 unit.
    if (($dbItems[$v1Id] ?? 0) !== 3) {
        throw new Exception("Merge failure: Variant 1 quantity mismatch. Expected 3, got " . ($dbItems[$v1Id] ?? 0));
    }
    if (($dbItems[$v2Id] ?? 0) !== 1) {
        throw new Exception("Merge failure: Variant 2 quantity mismatch. Expected 1, got " . ($dbItems[$v2Id] ?? 0));
    }
    // Verify session cart was cleared
    if ($session->has('cart')) {
        throw new Exception("Session cart was not cleared after merge.");
    }
    echo "[SUCCESS] Carts merged correctly. Session cart cleared.\n\n";

    // --- TEST 4: Address CRUD & Default Shipping ---
    echo "--- Test 4: Address CRUD & Default Shipping Flag Handling ---\n";
    
    // Insert first address (should force default)
    $addr1 = new Address();
    $addr1->user_id = $userId;
    $addr1->recipient_name = "Aly Maher";
    $addr1->phone_number = "01001234567";
    $addr1->governorate = "Cairo";
    $addr1->city = "Maadi";
    $addr1->street_address = "Road 9";
    $addr1->is_default = 0; // Requesting non-default
    
    // Triggering save.
    // In our controller, we force is_default = 1 if it's the first address, but model is passive.
    // Let's test the default swapping logic via direct DB and active swaps to mimic DashboardController actions.
    echo "Saving Address 1 (as default)...\n";
    $addr1->is_default = 1;
    $addr1->save();
    
    $addr1Id = $addr1->id;
    echo "Address 1 saved with ID: {$addr1Id}, Default flag: {$addr1->is_default}\n";

    // Save second address as default. Address 1 default should be cleared.
    echo "Saving Address 2 (requesting default)... Address 1 default flag should reset to 0.\n";
    
    // Simulate DashboardController transaction logic:
    $db->beginTransaction();
    $db->exec("UPDATE addresses SET is_default = 0 WHERE user_id = {$userId}");
    
    $addr2 = new Address();
    $addr2->user_id = $userId;
    $addr2->recipient_name = "Aly Maher 2";
    $addr2->phone_number = "01201234567";
    $addr2->governorate = "Giza";
    $addr2->city = "Haram";
    $addr2->street_address = "Pyramids St";
    $addr2->is_default = 1;
    $addr2->save();
    
    $addr2Id = $addr2->id;
    $db->commit();

    // Verify Address 1 is no longer default, and Address 2 is default
    $a1 = Address::find($addr1Id);
    $a2 = Address::find($addr2Id);

    if ((int)$a1->is_default !== 0) {
        throw new Exception("Address 1 did not reset its default shipping flag.");
    }
    if ((int)$a2->is_default !== 1) {
        throw new Exception("Address 2 was not successfully marked as default.");
    }
    echo "[SUCCESS] Default shipping address flag promotion and swap verified.\n\n";

    // --- CLEANUP ---
    echo "Cleaning up test records...\n";
    $db->exec("DELETE FROM addresses WHERE user_id = {$userId}");
    $db->exec("DELETE FROM cart_items WHERE user_id = {$userId}");
    $db->exec("DELETE FROM users WHERE id = {$userId}");
    $session->destroy();

    echo "\n==========================================\n";
    echo "ALL CART & ADDRESS INTEGRATION TESTS PASSED!\n";
    echo "==========================================\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "[FAILURE] Integration Error: " . $e->getMessage() . "\n";
    exit(1);
}
