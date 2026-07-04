<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Database;
use App\Config\Config;
use App\Services\CartService;
use App\Services\Payment\PaymentGatewayFactory;
use App\Core\EventDispatcher;
use App\Events\OrderPlacedEvent;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use PDO;
use Exception;

class CheckoutController extends Controller
{
    private Session $session;
    private CartService $cartService;
    private PDO $db;

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
        $this->cartService = new CartService();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Display checkout page (GET /checkout)
     */
    public function index(): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->session->setFlash('error', 'You must log in to proceed to checkout.');
            $this->redirect('/login');
        }

        $cartItems = $this->cartService->getCart();
        if (empty($cartItems)) {
            $this->session->setFlash('error', 'Your cart is empty.');
            $this->redirect('/cart');
        }

        $subtotal = 0.0;
        foreach ($cartItems as $item) {
            $subtotal += $item['subtotal'];
        }

        // Fetch user addresses
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE user_id = :user_id ORDER BY is_default DESC, created_at DESC");
        $stmt->execute(['user_id' => $user['id']]);
        $addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Determine default shipping fee
        $defaultShipping = 50.0; // Default fallback
        if (!empty($addresses)) {
            $defaultShipping = $this->calculateShipping($addresses[0]['governorate']);
        }

        // Egyptian VAT tax (14% VAT already included in subtotal by legal requirement, but let's list it)
        $tax = $subtotal * 0.14;

        $this->render('checkout/index', [
            'title' => 'Secure Checkout | Elze.eg',
            'user' => $user,
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'governorates' => $this->governorates,
            'subtotal' => $subtotal,
            'shipping_fee' => $defaultShipping,
            'tax' => $tax,
            'csrf_token' => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Validate coupon code via AJAX (POST /coupon/validate)
     */
    public function validateCoupon(): void
    {
        if (!$this->isPost()) {
            $this->json(['error' => 'Invalid request method.'], 405);
        }

        $data = $this->getPostData();
        $code = strtoupper(trim($data['coupon_code'] ?? ''));
        $subtotal = isset($data['subtotal']) ? (float)$data['subtotal'] : 0.0;

        if (empty($code)) {
            $this->json(['error' => 'Coupon code is required.'], 400);
        }

        // Query active coupon
        $stmt = $this->db->prepare("
            SELECT * FROM coupons 
            WHERE code = :code 
              AND is_active = 1 
              AND (starts_at IS NULL OR starts_at <= NOW()) 
              AND (expires_at IS NULL OR expires_at >= NOW()) 
            LIMIT 1
        ");
        $stmt->execute(['code' => $code]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$coupon) {
            $this->json(['error' => 'Invalid or expired coupon code.'], 400);
        }

        if ($subtotal < (float)$coupon['min_order_amount']) {
            $this->json([
                'error' => 'This coupon requires a minimum subtotal of ' . number_format($coupon['min_order_amount'], 0) . ' EGP.'
            ], 400);
        }

        $discount = 0.0;
        if ($coupon['discount_type'] === 'percent') {
            $discount = $subtotal * ((float)$coupon['discount_value'] / 100.0);
        } else {
            $discount = (float)$coupon['discount_value'];
        }

        // Cap discount at subtotal
        if ($discount > $subtotal) {
            $discount = $subtotal;
        }

        $this->json([
            'success' => true,
            'coupon_code' => $coupon['code'],
            'discount_amount' => $discount,
            'discount_formatted' => number_format($discount, 0) . ' EGP'
        ]);
    }

    /**
     * Process checkout form submission (POST /checkout)
     */
    public function process(): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        if (!$this->isPost()) {
            $this->redirect('/checkout');
        }

        $data = $this->getPostData();

        // 1. Verify CSRF
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF verification failed.');
            $this->redirect('/checkout');
        }

        // 2. Validate Cart Content
        $cartItems = $this->cartService->getCart();
        if (empty($cartItems)) {
            $this->session->setFlash('error', 'Your cart is empty. Please add products to check out.');
            $this->redirect('/cart');
        }

        // 3. Resolve Shipping Address
        $addressOption = $data['address_option'] ?? 'saved';
        $addressId = 0;
        $governorate = '';

        try {
            if ($addressOption === 'saved') {
                $savedAddressId = isset($data['address_id']) ? (int)$data['address_id'] : 0;
                
                // Verify saved address ownership
                $address = Address::find($savedAddressId);
                if (!$address || (int)$address->user_id !== (int)$user['id']) {
                    throw new Exception("Please select a valid saved shipping address.");
                }
                
                $addressId = $address->id;
                $governorate = $address->governorate;
            } else {
                // Insert a new address row
                $recipientName   = trim($data['new_recipient_name'] ?? '');
                $phoneNumber     = trim($data['new_phone_number'] ?? '');
                $governorate     = trim($data['new_governorate'] ?? '');
                $city            = trim($data['new_city'] ?? '');
                $streetAddress   = trim($data['new_street_address'] ?? '');
                $buildingDetails = trim($data['new_building_details'] ?? '');
                $saveAddress     = isset($data['save_address']) ? 1 : 0;

                // Validate inputs
                if (empty($recipientName) || empty($phoneNumber) || empty($governorate) || empty($city) || empty($streetAddress)) {
                    throw new Exception("Please fill out all required fields for the new address.");
                }
                if (!preg_match('/^01[0125][0-9]{8}$/', $phoneNumber)) {
                    throw new Exception("Invalid Egyptian mobile number format.");
                }
                if (!in_array($governorate, $this->governorates, true)) {
                    throw new Exception("Invalid governorate selected.");
                }

                // Save to database
                $address = new Address();
                $address->user_id = $user['id'];
                $address->recipient_name = $recipientName;
                $address->phone_number = $phoneNumber;
                $address->governorate = $governorate;
                $address->city = $city;
                $address->street_address = $streetAddress;
                $address->building_details = !empty($buildingDetails) ? $buildingDetails : null;
                $address->is_default = 0; // Don't disrupt previous default address unless checked
                
                if (!$address->save()) {
                    throw new Exception("Failed to save shipping address details.");
                }

                $addressId = $address->id;
            }

            // 4. Calculate Subtotal, Shipping Fee, and Discounts
            $subtotal = 0.0;
            foreach ($cartItems as $item) {
                $subtotal += $item['subtotal'];
            }

            $shippingFee = $this->calculateShipping($governorate);
            
            // Validate Coupon Discount
            $discount = 0.0;
            $couponCode = strtoupper(trim($data['coupon_code'] ?? ''));
            if (!empty($couponCode)) {
                $stmt = $this->db->prepare("
                    SELECT * FROM coupons 
                    WHERE code = :code 
                      AND is_active = 1 
                      AND (starts_at IS NULL OR starts_at <= NOW()) 
                      AND (expires_at IS NULL OR expires_at >= NOW()) 
                    LIMIT 1
                ");
                $stmt->execute(['code' => $couponCode]);
                $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($coupon && $subtotal >= (float)$coupon['min_order_amount']) {
                    if ($coupon['discount_type'] === 'percent') {
                        $discount = $subtotal * ((float)$coupon['discount_value'] / 100.0);
                    } else {
                        $discount = (float)$coupon['discount_value'];
                    }
                    $discount = min($discount, $subtotal);
                }
            }

            // VAT is included in the subtotal
            $taxAmount = $subtotal * 0.14;
            $totalAmount = $subtotal + $shippingFee - $discount;

            // 5. Enforce Inventory Stock Verification
            foreach ($cartItems as $item) {
                $stmt = $this->db->prepare("SELECT stock, size, color FROM product_variants WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $item['variant_id']]);
                $pv = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$pv || (int)$pv['stock'] < $item['quantity']) {
                    throw new Exception("Cannot place order. The variant (" . ($pv ? $pv['size'] . " - " . $pv['color'] : "Unknown") . ") does not have enough stock available.");
                }
            }

            // 6. Begin Transaction to Create Order
            $this->db->beginTransaction();

            // Unique Order Number: ELZE-2026-XXXX
            $orderNumber = 'ELZE-2026-' . strtoupper(bin2hex(random_bytes(3)));

            $order = new Order();
            $order->user_id = $user['id'];
            $order->order_number = $orderNumber;
            $order->subtotal = $subtotal;
            $order->shipping_fee = $shippingFee;
            $order->discount_amount = $discount;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $totalAmount;
            $order->status = 'pending';
            $order->payment_method = $data['payment_method'] ?? 'cod';
            $order->payment_status = 'pending';
            $order->shipping_address_id = $addressId;
            $order->notes = !empty($data['notes']) ? trim($data['notes']) : null;

            if (!$order->save()) {
                throw new Exception("Failed to record order header.");
            }

            // Save order item lines
            $orderItemsModels = [];
            foreach ($cartItems as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item['product_id'];
                $orderItem->variant_id = $item['variant_id'];
                $orderItem->quantity = $item['quantity'];
                $orderItem->unit_price = $item['price'];
                $orderItem->total_price = $item['subtotal'];
                
                if (!$orderItem->save()) {
                    throw new Exception("Failed to record order item line.");
                }
                
                $orderItemsModels[] = $orderItem;
            }

            // Clear Cart items from DB/Session
            if ($user['id']) {
                $stmt = $this->db->prepare("DELETE FROM cart_items WHERE user_id = :u");
                $stmt->execute(['u' => $user['id']]);
            }
            $this->session->remove('cart');

            $this->db->commit();

            // 7. Dispatch Observer OrderPlacedEvent
            $event = new OrderPlacedEvent($order, $orderItemsModels);
            EventDispatcher::getInstance()->dispatch($event);

            // 8. Execute Payment Strategy via Factory
            $paymentMethod = $data['payment_method'] ?? 'cod';
            $paymentStrategy = PaymentGatewayFactory::create($paymentMethod);
            $paymentStrategy->pay($order);

            // 9. Redirect based on strategy
            if ($paymentMethod === 'instapay') {
                $this->redirect('/checkout/instapay/' . $order->id);
            } else {
                $this->session->setFlash('success', 'Order placed successfully! Order #' . $orderNumber);
                $this->redirect('/dashboard/orders/' . $order->id);
            }

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->session->setFlash('error', $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    /**
     * Show InstaPay instructions panel (GET /checkout/instapay/{id})
     */
    public function instapay(string $id): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        $order = Order::find((int)$id);
        if (!$order || (int)$order->user_id !== (int)$user['id']) {
            $this->session->setFlash('error', 'Order not found.');
            $this->redirect('/dashboard');
        }

        // If already paid or verified, redirect to order detail
        if ($order->payment_method !== 'instapay' || in_array($order->payment_status, ['paid', 'pending_verification'], true)) {
            $this->redirect('/dashboard/orders/' . $order->id);
        }

        // Load InstaPay merchant config for display in the payment page
        $config = Config::getInstance();
        $instapayConfig = [
            'merchant_name' => $config->get('instapay.merchant_name', 'Elze.eg'),
            'ipa_address'   => $config->get('instapay.ipa_address', 'elze@instapay'),
            'phone_number'  => $config->get('instapay.phone_number', ''),
        ];

        $this->render('checkout/instapay', [
            'title'         => 'InstaPay Payment | Elze.eg',
            'order'         => $order,
            'instapay'      => $instapayConfig,
            'csrf_token'    => $this->session->getCsrfToken()
        ]);
    }

    /**
     * Verify InstaPay reference submission (POST /checkout/instapay/verify/{id})
     */
    public function verifyInstapay(string $id): void
    {
        $user = $this->session->get('user');
        if (!$user) {
            $this->redirect('/login');
        }

        if (!$this->isPost()) {
            $this->redirect('/checkout/instapay/' . $id);
        }

        $data = $this->getPostData();

        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF verification failed.');
            $this->redirect('/checkout/instapay/' . $id);
        }

        $order = Order::find((int)$id);
        if (!$order || (int)$order->user_id !== (int)$user['id']) {
            $this->session->setFlash('error', 'Order not found.');
            $this->redirect('/dashboard');
        }

        // Duplicate submission prevention: reject if reference already submitted
        if (in_array($order->payment_status, ['pending_verification', 'paid'], true)) {
            $this->session->setFlash('info', 'Payment reference already submitted. Awaiting admin verification.');
            $this->redirect('/dashboard/orders/' . $order->id);
        }

        $reference = trim($data['reference_code'] ?? '');
        if (empty($reference) || !preg_match('/^[A-Za-z0-9]{6,20}$/', $reference)) {
            $this->session->setFlash('error', 'Invalid transaction reference. Must be 6–20 alphanumeric characters.');
            $this->redirect('/checkout/instapay/' . $id);
        }

        try {
            // Invoke Payment Strategy with reference code — sets to pending_verification
            $strategy = PaymentGatewayFactory::create('instapay');
            if ($strategy->pay($order, ['reference_code' => $reference])) {
                $this->session->setFlash('success', '✓ Payment reference submitted successfully! Our team will verify your payment within 1–2 business hours.');
                $this->redirect('/dashboard/orders/' . $order->id);
            } else {
                throw new Exception('Unable to save payment reference. Please try again.');
            }
        } catch (Exception $e) {
            $this->session->setFlash('error', $e->getMessage());
            $this->redirect('/checkout/instapay/' . $id);
        }
    }

    /**
     * Helper to compute Egyptian governorate shipping costs
     */
    private function calculateShipping(string $governorate): float
    {
        $cairoGiza = ['Cairo', 'Giza'];
        $deltaCanal = ['Alexandria', 'Beheira', 'Gharbia', 'Kafr El Sheikh', 'Dakahlia', 'Damietta', 'Qalyubia', 'Sharqia', 'Monufia', 'Ismailia', 'Port Said', 'Suez'];
        $upperEgypt = ['Fayoum', 'Beni Suef', 'Minya', 'Assiut', 'Sohag', 'Qena', 'Luxor', 'Aswan'];
        
        if (in_array($governorate, $cairoGiza, true)) {
            return 50.00;
        } elseif (in_array($governorate, $deltaCanal, true)) {
            return 65.00;
        } elseif (in_array($governorate, $upperEgypt, true)) {
            return 80.00;
        } else {
            return 100.00; // Frontier / Sinai / Matrouh / Red Sea
        }
    }
}
