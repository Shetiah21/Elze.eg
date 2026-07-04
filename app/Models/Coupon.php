<?php

namespace App\Models;

use App\Core\Model;

class Coupon extends Model
{
    protected static string $table = 'coupons';

    public ?int $id = null;
    public string $code = '';
    public string $discount_type = 'fixed';
    public float $discount_value = 0.00;
    public float $min_order_amount = 0.00;
    public ?string $starts_at = null;
    public ?string $expires_at = null;
    public int $max_uses = 100;
    public int $uses_count = 0;
    public int $is_active = 1;
    public ?string $created_at = null;
}
