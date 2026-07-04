<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Services\MockMailLogger;
use Exception;
use PDO;

class OrderManagementService
{
    private PDO $db;

    /** @var array<string, list<string>> */
    private array $transitions = [
        'pending'    => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered', 'cancelled'],
        'delivered'  => [],
        'cancelled'  => [],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllowedTransitions(string $currentStatus): array
    {
        return $this->transitions[$currentStatus] ?? [];
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, $this->getAllowedTransitions($from), true);
    }

    /**
     * Accept a pending order: validate stock, deduct inventory, set processing.
     */
    public function acceptOrder(int $orderId): Order
    {
        $order = Order::find($orderId);
        if (!$order) {
            throw new Exception('Order not found.');
        }

        if ($order->status !== 'pending') {
            throw new Exception('Only pending orders can be accepted.');
        }

        $items = $this->getOrderItems($orderId);
        $customer = $this->getCustomerEmail($order->user_id);

        $this->db->beginTransaction();

        try {
            if (!$this->isInventoryDeducted($order)) {
                $this->validateStock($items);
                $this->deductInventory($items);
                $this->markInventoryDeducted($order);
            }

            $order->status = 'processing';
            if (!$order->save()) {
                throw new Exception('Failed to update order status.');
            }

            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        $this->logStatusNotification(
            $customer['email'],
            $order->order_number,
            'processing',
            'accepted and is now being processed'
        );

        return $order;
    }

    /**
     * Reject a pending order: set cancelled without inventory changes.
     */
    public function rejectOrder(int $orderId): Order
    {
        $order = Order::find($orderId);
        if (!$order) {
            throw new Exception('Order not found.');
        }

        if ($order->status !== 'pending') {
            throw new Exception('Only pending orders can be rejected.');
        }

        $customer = $this->getCustomerEmail($order->user_id);

        $order->status = 'cancelled';
        if (!$order->save()) {
            throw new Exception('Failed to reject order.');
        }

        $this->logStatusNotification(
            $customer['email'],
            $order->order_number,
            'cancelled',
            'rejected by our team'
        );

        return $order;
    }

    /**
     * Update order status with transition validation and inventory restore on cancel.
     */
    public function updateStatus(int $orderId, string $newStatus, ?string $trackingNumber = null): Order
    {
        $order = Order::find($orderId);
        if (!$order) {
            throw new Exception('Order not found.');
        }

        $current = $order->status;
        if ($current === $newStatus) {
            if ($trackingNumber !== null) {
                $order->tracking_number = $trackingNumber !== '' ? $trackingNumber : null;
                $order->save();
            }
            return $order;
        }

        if (!$this->canTransition($current, $newStatus)) {
            throw new Exception(
                "Invalid status transition from '" . ucfirst($current) . "' to '" . ucfirst($newStatus) . "'."
            );
        }

        $items = $this->getOrderItems($orderId);
        $customer = $this->getCustomerEmail($order->user_id);

        $this->db->beginTransaction();

        try {
            if ($newStatus === 'cancelled' && $this->isInventoryDeducted($order)) {
                $this->restoreInventory($items);
                $this->clearInventoryDeducted($order);
            }

            if ($newStatus === 'processing' && !$this->isInventoryDeducted($order)) {
                $this->validateStock($items);
                $this->deductInventory($items);
                $this->markInventoryDeducted($order);
            }

            $order->status = $newStatus;
            if ($trackingNumber !== null) {
                $order->tracking_number = $trackingNumber !== '' ? $trackingNumber : null;
            }

            if (!$order->save()) {
                throw new Exception('Failed to save order.');
            }

            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        $this->logStatusNotification(
            $customer['email'],
            $order->order_number,
            $newStatus,
            'updated to ' . ucfirst($newStatus)
        );

        return $order;
    }

    private function getOrderItems(int $orderId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCustomerEmail(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT name, email FROM users WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            throw new Exception('Customer not found.');
        }
        return $row;
    }

    private function validateStock(array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['variant_id'])) {
                continue;
            }
            $stmt = $this->db->prepare("
                SELECT stock, size, color FROM product_variants WHERE id = :id LIMIT 1
            ");
            $stmt->execute(['id' => $item['variant_id']]);
            $variant = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$variant || (int) $variant['stock'] < (int) $item['quantity']) {
                $label = $variant
                    ? $variant['size'] . ' / ' . $variant['color']
                    : 'Unknown variant';
                throw new Exception(
                    "Insufficient stock for variant ({$label}). Cannot accept this order."
                );
            }
        }
    }

    private function deductInventory(array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['variant_id'])) {
                continue;
            }
            $stmt = $this->db->prepare("
                UPDATE product_variants
                SET stock = stock - :qty
                WHERE id = :id AND stock >= :min_qty
            ");
            $stmt->execute([
                'qty'     => (int) $item['quantity'],
                'min_qty' => (int) $item['quantity'],
                'id'      => (int) $item['variant_id'],
            ]);
            if ($stmt->rowCount() === 0) {
                throw new Exception('Inventory deduction failed due to insufficient stock.');
            }
        }
    }

    private function restoreInventory(array $items): void
    {
        foreach ($items as $item) {
            if (empty($item['variant_id'])) {
                continue;
            }
            $stmt = $this->db->prepare("
                UPDATE product_variants SET stock = stock + :qty WHERE id = :id
            ");
            $stmt->execute([
                'qty' => (int) $item['quantity'],
                'id'  => (int) $item['variant_id'],
            ]);
        }
    }

    /**
     * Track whether inventory was deducted for this order (stored in notes marker).
     */
    private function isInventoryDeducted(Order $order): bool
    {
        return str_contains($order->notes ?? '', '[INV_DEDUCTED]');
    }

    private function markInventoryDeducted(Order $order): void
    {
        if (!$this->isInventoryDeducted($order)) {
            $order->notes = trim(($order->notes ?? '') . ' [INV_DEDUCTED]');
        }
    }

    private function clearInventoryDeducted(Order $order): void
    {
        $order->notes = trim(str_replace('[INV_DEDUCTED]', '', $order->notes ?? '')) ?: null;
    }

    private function logStatusNotification(
        string $email,
        string $orderNumber,
        string $status,
        string $actionDescription
    ): void {
        MockMailLogger::log(
            $email,
            "Elze.eg Order Update — {$orderNumber}",
            "Dear Customer,\n\nYour order {$orderNumber} has been {$actionDescription}.\n\nUpdated Status: " . strtoupper($status) . "\n\nThank you for shopping at Elze.eg.\nElze.eg Team"
        );
    }
}
