<?php

namespace App\Models;

use App\Core\Model;

class ProductVariant extends Model
{
    protected static string $table = 'product_variants';

    public ?int $id = null;
    public int $product_id = 0;
    public string $size = '';
    public string $color = '';
    public int $stock = 0;
    public float $price_modifier = 0.00;
    public ?string $created_at = null;
}
