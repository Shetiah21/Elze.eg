<?php

namespace App\Observers;

use App\Events\OrderPlacedEvent;

class InventoryObserver
{
    /**
     * Inventory is deducted when an admin accepts an order (pending → processing).
     * See OrderManagementService::acceptOrder().
     */
    public function handle(OrderPlacedEvent $event): void
    {
        // Deferred to admin order approval workflow.
    }
}
