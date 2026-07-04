<?php

namespace App\Observers;

use App\Events\OrderPlacedEvent;
use App\Core\Database;

class InventoryObserver
{
    /**
     * Handle the OrderPlacedEvent to decrement stock
     * 
     * @param OrderPlacedEvent $event
     */
    public function handle(OrderPlacedEvent $event): void
    {
        $db = Database::getInstance()->getConnection();
        
        foreach ($event->items as $item) {
            if (!empty($item->variant_id)) {
                $stmt = $db->prepare("UPDATE product_variants SET stock = GREATEST(0, stock - :qty) WHERE id = :id");
                $stmt->execute([
                    'qty' => (int)$item->quantity,
                    'id' => (int)$item->variant_id
                ]);
            }
        }
    }
}
