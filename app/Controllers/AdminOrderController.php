<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Order;
use App\Services\OrderManagementService;
use Exception;
use PDO;

class AdminOrderController extends AdminController
{
    private array $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    private OrderManagementService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderManagementService();
    }

    public function index(): void
    {
        $this->requireAdmin();
        $status = trim($this->getQueryParams()['status'] ?? '');

        $sql = "
            SELECT o.*, u.name AS customer_name, u.email AS customer_email
            FROM orders o
            JOIN users u ON u.id = o.user_id
        ";
        $params = [];

        if ($status !== '' && in_array($status, $this->validStatuses, true)) {
            $sql .= " WHERE o.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY FIELD(o.status, 'pending', 'processing', 'shipped', 'delivered', 'cancelled'), o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $pendingCount = (int) $this->db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

        $this->renderAdmin('admin/orders/index', [
            'title' => 'Orders | Admin',
            'active_section' => 'orders',
            'orders' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'current_status' => $status,
            'statuses' => $this->validStatuses,
            'pending_count' => $pendingCount,
        ]);
    }

    public function detail(string $id): void
    {
        $this->requireAdmin();
        $order = Order::find((int) $id);
        if (!$order) {
            $this->session->setFlash('error', 'Order not found.');
            $this->redirect('/admin/orders');
        }

        $customer = $this->fetchCustomer($order->user_id);
        $address = $this->fetchAddress($order->shipping_address_id);
        $items = $this->fetchOrderItems((int) $id);

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();
            $newStatus = $data['status'] ?? '';
            $tracking = trim($data['tracking_number'] ?? '');

            try {
                $this->orderService->updateStatus((int) $id, $newStatus, $tracking);
                $this->session->setFlash('success', 'Order updated successfully.');
            } catch (Exception $e) {
                $this->session->setFlash('error', $e->getMessage());
            }
            $this->redirect('/admin/orders/' . $id);
        }

        $this->renderAdmin('admin/orders/detail', [
            'title' => 'Order ' . $order->order_number . ' | Admin',
            'active_section' => 'orders',
            'order' => $order,
            'customer' => $customer,
            'address' => $address,
            'items' => $items,
            'allowed_transitions' => $this->orderService->getAllowedTransitions($order->status),
        ]);
    }

    public function accept(string $id): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $data = $this->getPostData();
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->json(['success' => false, 'message' => 'CSRF validation failed.'], 403);
        }

        try {
            $order = $this->orderService->acceptOrder((int) $id);
            $this->json([
                'success' => true,
                'message' => 'Order accepted and moved to Processing.',
                'status' => $order->status,
                'order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function reject(string $id): void
    {
        $this->requireAdmin();
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request method.'], 405);
        }

        $data = $this->getPostData();
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->json(['success' => false, 'message' => 'CSRF validation failed.'], 403);
        }

        try {
            $order = $this->orderService->rejectOrder((int) $id);
            $this->json([
                'success' => true,
                'message' => 'Order has been rejected and cancelled.',
                'status' => $order->status,
                'order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function fetchCustomer(int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchAddress(int $addressId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM addresses WHERE id = :id");
        $stmt->execute(['id' => $addressId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function fetchOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name AS product_name, pv.size, pv.color
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN product_variants pv ON pv.id = oi.variant_id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
