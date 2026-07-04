<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Models\Address;
use PDO;
use Exception;

class DashboardController extends Controller
{
    private Session $session;
    private PDO $db;

    // List of allowed Egyptian governorates for validation
    private array $governorates = [
        'Cairo', 'Giza', 'Alexandria', 'Qalyubia', 'Sharqia', 
        'Dakahlia', 'Beheira', 'Gharbia', 'Monufia', 'Minya', 
        'Qena', 'Sohag', 'Assiut', 'Suez', 'Port Said', 'Damietta', 
        'Ismailia', 'Fayoum', 'Beni Suef', 'Aswan', 'Luxor', 
        'Red Sea', 'Matrouh', 'North Sinai', 'South Sinai', 
        'New Valley', 'Kafr El Sheikh'
    ];

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Render a dashboard view with the premium dashboard layout.
     */
    private function renderDashboard(string $view, array $data = []): void
    {
        $data['csrf_token'] = $this->session->getCsrfToken();
        $this->render($view, $data, 'dashboard');
    }

    /**
     * Require authenticated user or redirect to login.
     */
    private function requireUser(): array
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->session->setFlash('error', 'You must be logged in to access your dashboard.');
            $this->redirect('/login');
        }
        return $user;
    }

    /**
     * Fetch overview statistics for the dashboard home.
     */
    private function getOverviewStats(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) AS total_orders,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_orders,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS completed_orders
            FROM orders WHERE user_id = :uid
        ");
        $stmt->execute(['uid' => $userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $addrStmt = $this->db->prepare("SELECT COUNT(*) FROM addresses WHERE user_id = :uid");
        $addrStmt->execute(['uid' => $userId]);

        return [
            'total_orders' => (int) ($stats['total_orders'] ?? 0),
            'pending_orders' => (int) ($stats['pending_orders'] ?? 0),
            'completed_orders' => (int) ($stats['completed_orders'] ?? 0),
            'saved_addresses' => (int) $addrStmt->fetchColumn(),
        ];
    }

    /**
     * Display the User Account Dashboard Overview
     */
    public function index(): void
    {
        $user = $this->requireUser();
        $userId = (int) $user['id'];

        $stats = $this->getOverviewStats($userId);

        $recentStmt = $this->db->prepare("
            SELECT * FROM orders WHERE user_id = :uid ORDER BY created_at DESC LIMIT 5
        ");
        $recentStmt->execute(['uid' => $userId]);
        $recentOrders = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

        $defaultAddrStmt = $this->db->prepare("
            SELECT * FROM addresses WHERE user_id = :uid ORDER BY is_default DESC, created_at DESC LIMIT 1
        ");
        $defaultAddrStmt->execute(['uid' => $userId]);
        $defaultAddress = $defaultAddrStmt->fetch(PDO::FETCH_ASSOC) ?: null;

        $userRow = $this->db->prepare("SELECT created_at FROM users WHERE id = :id");
        $userRow->execute(['id' => $userId]);
        $memberSince = $userRow->fetchColumn();

        $phone = $defaultAddress['phone_number'] ?? null;

        $this->renderDashboard('dashboard/index', [
            'title' => 'My Account | Elze.eg',
            'user' => $user,
            'active_tab' => 'dashboard',
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'default_address' => $defaultAddress,
            'member_since' => $memberSince,
            'phone' => $phone,
        ]);
    }

    /**
     * Display user profile details (GET /dashboard/profile)
     */
    public function profile(): void
    {
        $user = $this->requireUser();
        $userId = (int) $user['id'];

        $defaultAddrStmt = $this->db->prepare("
            SELECT phone_number FROM addresses WHERE user_id = :uid ORDER BY is_default DESC LIMIT 1
        ");
        $defaultAddrStmt->execute(['uid' => $userId]);
        $phone = $defaultAddrStmt->fetchColumn() ?: null;

        $userRow = $this->db->prepare("SELECT created_at FROM users WHERE id = :id");
        $userRow->execute(['id' => $userId]);
        $memberSince = $userRow->fetchColumn();

        $this->renderDashboard('dashboard/profile', [
            'title' => 'My Profile | Elze.eg',
            'user' => $user,
            'active_tab' => 'profile',
            'phone' => $phone,
            'member_since' => $memberSince,
        ]);
    }

    /**
     * Wishlist placeholder (GET /dashboard/wishlist)
     */
    public function wishlist(): void
    {
        $user = $this->requireUser();
        $this->renderDashboard('dashboard/wishlist', [
            'title' => 'Wishlist | Elze.eg',
            'user' => $user,
            'active_tab' => 'wishlist',
        ]);
    }

    /**
     * Settings placeholder (GET /dashboard/settings)
     */
    public function settings(): void
    {
        $user = $this->requireUser();
        $this->renderDashboard('dashboard/settings', [
            'title' => 'Settings | Elze.eg',
            'user' => $user,
            'active_tab' => 'settings',
        ]);
    }

    /**
     * Display User Order History (GET /dashboard/orders)
     */
    public function orders(): void
    {
        $user = $this->requireUser();

        $stmt = $this->db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $user['id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderDashboard('dashboard/orders', [
            'title' => 'Order History | Elze.eg',
            'user' => $user,
            'orders' => $orders,
            'active_tab' => 'orders'
        ]);
    }

    /**
     * Display a specific order with details and progress bar (GET /dashboard/orders/{id})
     */
    public function orderDetail(string $id): void
    {
        $user = $this->requireUser();

        $orderId = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order || (int)$order['user_id'] !== (int)$user['id']) {
            $this->session->setFlash('error', 'Order not found.');
            $this->redirect('/dashboard/orders');
        }

        // Fetch shipping address details
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $order['shipping_address_id']]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch order items with product name & variant details
        $stmt = $this->db->prepare("
            SELECT 
                oi.*, 
                p.name AS product_name, 
                p.slug AS product_slug,
                pv.size, 
                pv.color
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN product_variants pv ON pv.id = oi.variant_id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->renderDashboard('dashboard/order_detail', [
            'title' => 'Order Details #' . $order['order_number'] . ' | Elze.eg',
            'user' => $user,
            'order' => $order,
            'items' => $items,
            'address' => $address,
            'active_tab' => 'orders'
        ]);
    }

    /**
     * Render dynamic HTML receipt invoice (GET /orders/receipt/{id})
     */
    public function receipt(string $id): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        $orderId = (int)$id;
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order || (int)$order['user_id'] !== (int)$user['id']) {
            $this->session->setFlash('error', 'Order not found.');
            $this->redirect('/dashboard/orders');
        }

        // Fetch shipping address
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $order['shipping_address_id']]);
        $address = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch items
        $stmt = $this->db->prepare("
            SELECT 
                oi.*, 
                p.name AS product_name, 
                pv.size, 
                pv.color
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN product_variants pv ON pv.id = oi.variant_id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Render using no layout wrapper so it displays print-friendly output directly
        $this->render('dashboard/receipt', [
            'order' => $order,
            'items' => $items,
            'address' => $address,
            'user' => $user
        ], '');
    }

    /**
     * List user saved addresses (GET /dashboard/addresses)
     */
    public function addresses(): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->session->setFlash('error', 'You must be logged in to access your address book.');
            $this->redirect('/login');
        }

        // Fetch addresses
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC");
        $stmt->execute(['user_id' => $user['id']]);
        $addresses = $stmt->fetchAll();

        $this->renderDashboard('dashboard/addresses', [
            'title' => 'My Saved Addresses | Elze.eg',
            'user' => $user,
            'addresses' => $addresses,
            'governorates' => $this->governorates,
            'active_tab' => 'addresses',
        ]);
    }

    /**
     * Create Address (POST /dashboard/addresses/create)
     */
    public function createAddress(): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        if (!$this->isPost()) {
            $this->redirect('/dashboard/addresses');
        }

        $data = $this->getPostData();

        // 1. Validate CSRF Token
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF verification failed. Please try again.');
            $this->redirect('/dashboard/addresses');
        }

        // 2. Extract and sanitize inputs
        $recipientName = trim($data['recipient_name'] ?? '');
        $phoneNumber   = trim($data['phone_number'] ?? '');
        $governorate   = trim($data['governorate'] ?? '');
        $city          = trim($data['city'] ?? '');
        $streetAddress = trim($data['street_address'] ?? '');
        $buildingDetails = trim($data['building_details'] ?? '');
        $isDefault     = isset($data['is_default']) ? 1 : 0;

        // 3. Input Validation
        try {
            $this->validateAddressInput($recipientName, $phoneNumber, $governorate, $city, $streetAddress, $buildingDetails);

            // If it is the first address, force it to be default
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM addresses WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user['id']]);
            $countRow = $stmt->fetch();
            if ((int)$countRow['count'] === 0) {
                $isDefault = 1;
            }

            // Begin transaction for default status integrity
            $this->db->beginTransaction();

            if ($isDefault === 1) {
                // Remove default flag from all other user addresses
                $stmt = $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $user['id']]);
            }

            // Insert new address
            $address = new Address();
            $address->user_id = (int)$user['id'];
            $address->recipient_name = $recipientName;
            $address->phone_number = $phoneNumber;
            $address->governorate = $governorate;
            $address->city = $city;
            $address->street_address = $streetAddress;
            $address->building_details = !empty($buildingDetails) ? $buildingDetails : null;
            $address->is_default = $isDefault;

            if ($address->save()) {
                $this->db->commit();
                $this->session->setFlash('success', 'Address successfully added to your dashboard.');
            } else {
                throw new Exception("Unable to save the address.");
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/dashboard/addresses');
    }

    /**
     * Update Address (POST /dashboard/addresses/update/{id})
     */
    public function updateAddress(string $id): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        if (!$this->isPost()) {
            $this->redirect('/dashboard/addresses');
        }

        $data = $this->getPostData();

        // 1. Validate CSRF
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF verification failed. Please try again.');
            $this->redirect('/dashboard/addresses');
        }

        // 2. Fetch existing address and verify ownership
        $address = Address::find((int)$id);
        if (!$address || (int)$address->user_id !== (int)$user['id']) {
            $this->session->setFlash('error', 'Address not found or unauthorized.');
            $this->redirect('/dashboard/addresses');
        }

        // 3. Extract and validate input data
        $recipientName = trim($data['recipient_name'] ?? '');
        $phoneNumber   = trim($data['phone_number'] ?? '');
        $governorate   = trim($data['governorate'] ?? '');
        $city          = trim($data['city'] ?? '');
        $streetAddress = trim($data['street_address'] ?? '');
        $buildingDetails = trim($data['building_details'] ?? '');
        $isDefault     = isset($data['is_default']) ? 1 : 0;

        try {
            $this->validateAddressInput($recipientName, $phoneNumber, $governorate, $city, $streetAddress, $buildingDetails);

            // Begin Transaction
            $this->db->beginTransaction();

            // If marking as default, clear previous default
            if ($isDefault === 1 && $address->is_default === 0) {
                $stmt = $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
                $stmt->execute(['user_id' => $user['id']]);
            }

            // If unmarking default but it is currently default, check if we have other addresses to make default
            if ($isDefault === 0 && $address->is_default === 1) {
                // Cannot unmark default if it's the only one
                $stmt = $this->db->prepare("SELECT id FROM addresses WHERE user_id = :user_id AND id != :id LIMIT 1");
                $stmt->execute(['user_id' => $user['id'], 'id' => $address->id]);
                $other = $stmt->fetch();
                if ($other) {
                    // Set other as default
                    $stmt = $this->db->prepare("UPDATE addresses SET is_default = 1 WHERE id = :id");
                    $stmt->execute(['id' => $other['id']]);
                } else {
                    // Force remain default if it is the only address
                    $isDefault = 1;
                }
            }

            $address->recipient_name = $recipientName;
            $address->phone_number = $phoneNumber;
            $address->governorate = $governorate;
            $address->city = $city;
            $address->street_address = $streetAddress;
            $address->building_details = !empty($buildingDetails) ? $buildingDetails : null;
            $address->is_default = $isDefault;

            if ($address->save()) {
                $this->db->commit();
                $this->session->setFlash('success', 'Address details updated successfully.');
            } else {
                throw new Exception("Unable to update address.");
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/dashboard/addresses');
    }

    /**
     * Delete Address (POST /dashboard/addresses/delete/{id})
     */
    public function deleteAddress(string $id): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        if (!$this->isPost()) {
            $this->redirect('/dashboard/addresses');
        }

        $data = $this->getPostData();

        // 1. Validate CSRF
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF verification failed.');
            $this->redirect('/dashboard/addresses');
        }

        // 2. Fetch address and verify ownership
        $address = Address::find((int)$id);
        if (!$address || (int)$address->user_id !== (int)$user['id']) {
            $this->session->setFlash('error', 'Address not found or unauthorized.');
            $this->redirect('/dashboard/addresses');
        }

        try {
            $this->db->beginTransaction();

            $wasDefault = (int)$address->is_default === 1;

            if ($address->delete()) {
                // If the deleted address was default, promote another address to default
                if ($wasDefault) {
                    $stmt = $this->db->prepare("SELECT id FROM addresses WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute(['user_id' => $user['id']]);
                    $other = $stmt->fetch();
                    if ($other) {
                        $stmt = $this->db->prepare("UPDATE addresses SET is_default = 1 WHERE id = :id");
                        $stmt->execute(['id' => $other['id']]);
                    }
                }
                $this->db->commit();
                $this->session->setFlash('success', 'Address deleted successfully.');
            } else {
                throw new Exception("Unable to delete address.");
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/dashboard/addresses');
    }

    /**
     * Set Address as Default (POST /dashboard/addresses/make-default/{id})
     */
    public function makeDefaultAddress(string $id): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        if (!$this->isPost()) {
            $this->redirect('/dashboard/addresses');
        }

        $data = $this->getPostData();

        // 1. Validate CSRF
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF verification failed.');
            $this->redirect('/dashboard/addresses');
        }

        // 2. Fetch address and verify ownership
        $address = Address::find((int)$id);
        if (!$address || (int)$address->user_id !== (int)$user['id']) {
            $this->session->setFlash('error', 'Address not found or unauthorized.');
            $this->redirect('/dashboard/addresses');
        }

        try {
            $this->db->beginTransaction();

            // Reset other default flags
            $stmt = $this->db->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $user['id']]);

            // Set this one as default
            $address->is_default = 1;
            if ($address->save()) {
                $this->db->commit();
                $this->session->setFlash('success', 'Default shipping address successfully updated.');
            } else {
                throw new Exception("Unable to make address default.");
            }
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->session->setFlash('error', $e->getMessage());
        }

        $this->redirect('/dashboard/addresses');
    }

    /**
     * Validation helper for address inputs
     */
    private function validateAddressInput(string $recipientName, string $phoneNumber, string $governorate, string $city, string $streetAddress, string $buildingDetails): void
    {
        if (empty($recipientName)) {
            throw new Exception("Recipient Name is required.");
        }
        if (strlen($recipientName) > 255) {
            throw new Exception("Recipient Name cannot exceed 255 characters.");
        }

        if (empty($phoneNumber)) {
            throw new Exception("Phone Number is required.");
        }
        // Validate Egyptian mobile phone number formats: e.g. 010, 011, 012, 015 followed by 8 digits
        if (!preg_match('/^01[0125][0-9]{8}$/', $phoneNumber)) {
            throw new Exception("Invalid Egyptian phone number. Must be 11 digits starting with 010, 011, 012, or 015.");
        }

        if (empty($governorate)) {
            throw new Exception("Governorate is required.");
        }
        if (!in_array($governorate, $this->governorates, true)) {
            throw new Exception("Invalid Egyptian governorate selected.");
        }

        if (empty($city)) {
            throw new Exception("City is required.");
        }
        if (strlen($city) > 100) {
            throw new Exception("City name cannot exceed 100 characters.");
        }

        if (empty($streetAddress)) {
            throw new Exception("Street Address is required.");
        }

        if (!empty($buildingDetails) && strlen($buildingDetails) > 255) {
            throw new Exception("Building details cannot exceed 255 characters.");
        }
    }
}
