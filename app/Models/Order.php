<?php

namespace App\Models;

use App\Core\Model;

class Order extends Model
{
    protected static string $table = 'orders';

    public ?int $id = null;
    public int $user_id = 0;
    public string $order_number = '';
    public float $subtotal = 0.00;
    public float $shipping_fee = 0.00;
    public float $discount_amount = 0.00;
    public float $tax_amount = 0.00;
    public float $total_amount = 0.00;
    public string $status = 'pending'; // pending, processing, shipped, delivered, cancelled
    public string $payment_method = 'cod'; // cod, instapay
    public string $payment_status = 'pending'; // pending, paid, failed
    public int $shipping_address_id = 0;
    public ?string $tracking_number = null;
    public ?string $payment_reference = null;
    public ?string $notes = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}
