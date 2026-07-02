<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected static string $table = 'categories';

    public ?int $id = null;
    public string $name = '';
    public string $slug = '';
    public ?string $created_at = null;
}
