<?php

namespace App\Observers;

use App\Events\OrderPlacedEvent;
use App\Models\User;
use App\Core\Database;
use App\Services\MockMailLogger;
use PDO;

class NotificationObserver
{
    /**
     * Handle the OrderPlacedEvent to log email invoice
     * 
     * @param OrderPlacedEvent $event
     */
    public function handle(OrderPlacedEvent $event): void
    {
        $order = $event->order;
        $db = Database::getInstance()->getConnection();
        
        // 1. Fetch User details
        $user = User::find($order->user_id);
        if (!$user) {
            return;
        }

        // 2. Fetch full item details for the order invoice printout
        $stmt = $db->prepare("
            SELECT 
                oi.quantity, 
                oi.unit_price, 
                oi.total_price,
                p.name AS product_name,
                pv.size,
                pv.color
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            LEFT JOIN product_variants pv ON pv.id = oi.variant_id
            WHERE oi.order_id = :order_id
        ");
        $stmt->execute(['order_id' => $order->id]);
        $itemsDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 3. Construct email body
        $timestamp = date('Y-m-d H:i:s');
        $divider = str_repeat('-', 60) . "\n";
        
        $emailContent = "Timestamp: {$timestamp}\n";
        $emailContent .= "To: {$user->email}\n";
        $emailContent .= "Subject: Order Placed Successfully - {$order->order_number}\n";
        $emailContent .= "Message:\n";
        $emailContent .= "Hi " . htmlspecialchars($user->name) . ",\n\n";
        $emailContent .= "Thank you for shopping at Elze.eg! Your order has been placed successfully.\n\n";
        $emailContent .= "Order Details:\n";
        $emailContent .= "Order Number  : {$order->order_number}\n";
        $emailContent .= "Payment Method: " . strtoupper($order->payment_method) . "\n";
        $emailContent .= "Payment Status: " . strtoupper($order->payment_status) . "\n";
        $emailContent .= "Order Status  : " . strtoupper($order->status) . "\n\n";
        $emailContent .= "Itemized Receipt:\n";
        $emailContent .= $divider;
        $emailContent .= sprintf("%-30s %-12s %-5s %-10s\n", "Item Name", "Variant", "Qty", "Total");
        $emailContent .= $divider;

        foreach ($itemsDetails as $item) {
            $variantStr = $item['size'] . ' / ' . $item['color'];
            // Shorten product name if too long for tabular format
            $prodName = strlen($item['product_name']) > 28 ? substr($item['product_name'], 0, 25) . '...' : $item['product_name'];
            $emailContent .= sprintf(
                "%-30s %-12s %-5d %-10s\n", 
                $prodName, 
                $variantStr, 
                $item['quantity'], 
                number_format($item['total_price'], 2) . ' EGP'
            );
        }
        
        $emailContent .= $divider;
        $emailContent .= sprintf("%40s: %-15s\n", "Subtotal", number_format($order->subtotal, 2) . ' EGP');
        if ($order->discount_amount > 0) {
            $emailContent .= sprintf("%40s: -%-15s\n", "Discount", number_format($order->discount_amount, 2) . ' EGP');
        }
        $emailContent .= sprintf("%40s: %-15s\n", "Shipping Fee", number_format($order->shipping_fee, 2) . ' EGP');
        $emailContent .= sprintf("%40s: %-15s\n", "Grand Total", number_format($order->total_amount, 2) . ' EGP');
        $emailContent .= $divider;
        $emailContent .= "\nWe hope to see you again soon!\nElze.eg Team\n";
        $emailContent .= str_repeat('=', 60) . "\n";

        MockMailLogger::appendRaw($emailContent);
    }
}
