<?php

namespace App\Models;

use App\Core\Model;

class OrderItem extends Model
{
    protected static string $table = 'order_items';

    public ?int $id = null;
    public int $order_id = 0;
    public int $product_id = 0;
    public ?int $variant_id = null;
    public int $quantity = 0;
    public float $unit_price = 0.00;
    public float $total_price = 0.00;
    public ?string $created_at = null;
}
