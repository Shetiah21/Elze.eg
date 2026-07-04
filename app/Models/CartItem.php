<?php

namespace App\Models;

use App\Core\Model;

class CartItem extends Model
{
    protected static string $table = 'cart_items';

    public ?int $id = null;
    public int $user_id = 0;
    public int $variant_id = 0;
    public int $quantity = 0;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}
