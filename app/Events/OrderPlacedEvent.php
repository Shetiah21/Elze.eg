<?php

namespace App\Events;

use App\Models\Order;

class OrderPlacedEvent
{
    public Order $order;
    public array $items; // Array of OrderItem objects

    public function __construct(Order $order, array $items)
    {
        $this->order = $order;
        $this->items = $items;
    }
}
