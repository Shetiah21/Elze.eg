<?php

namespace App\Models;

use App\Core\Model;

class ProductImage extends Model
{
    protected static string $table = 'product_images';

    public ?int $id = null;
    public int $product_id = 0;
    public string $image_path = '';
    public int $is_primary = 0;
    public ?string $created_at = null;
}
