<?php

namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected static string $table = 'products';

    public ?int $id = null;
    public int $category_id = 0;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public float $base_price = 0.00;
    public ?string $size_chart_details = null; // JSON string
    public int $is_active = 1;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}
