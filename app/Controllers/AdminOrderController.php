<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\Order;
use PDO;

class AdminOrderController extends AdminController
{
    private array $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

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

        $sql .= " ORDER BY o.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $this->renderAdmin('admin/orders/index', [
            'title' => 'Orders | Admin',
            'active_section' => 'orders',
            'orders' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'current_status' => $status,
            'statuses' => $this->validStatuses,
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

        $userStmt = $this->db->prepare("SELECT id, name, email FROM users WHERE id = :id");
        $userStmt->execute(['id' => $order->user_id]);
        $customer = $userStmt->fetch(PDO::FETCH_ASSOC);

        $addrStmt = $this->db->prepare("SELECT * FROM addresses WHERE id = :id");
        $addrStmt->execute(['id' => $order->shipping_address_id]);
        $address = $addrStmt->fetch(PDO::FETCH_ASSOC);

        $itemsStmt = $this->db->prepare("
            SELECT oi.*, p.name AS product_name, pv.size, pv.color
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN product_variants pv ON pv.id = oi.variant_id
            WHERE oi.order_id = :order_id
        ");
        $itemsStmt->execute(['order_id' => $id]);
        $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->isPost()) {
            $this->validateCsrf();
            $data = $this->getPostData();
            $newStatus = $data['status'] ?? '';
            $tracking = trim($data['tracking_number'] ?? '');

            if (!in_array($newStatus, $this->validStatuses, true)) {
                $this->session->setFlash('error', 'Invalid order status.');
                $this->redirect('/admin/orders/' . $id);
            }

            $order->status = $newStatus;
            $order->tracking_number = $tracking !== '' ? $tracking : null;
            $order->save();

            $this->session->setFlash('success', 'Order updated successfully.');
            $this->redirect('/admin/orders/' . $id);
        }

        $this->renderAdmin('admin/orders/detail', [
            'title' => 'Order ' . $order->order_number . ' | Admin',
            'active_section' => 'orders',
            'order' => $order,
            'customer' => $customer,
            'address' => $address,
            'items' => $items,
            'statuses' => $this->validStatuses,
        ]);
    }
}
