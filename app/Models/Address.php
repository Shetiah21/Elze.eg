<?php

namespace App\Models;

use App\Core\Model;

class Address extends Model
{
    protected static string $table = 'addresses';

    public ?int $id = null;
    public int $user_id = 0;
    public string $recipient_name = '';
    public string $phone_number = '';
    public string $governorate = '';
    public string $city = '';
    public string $street_address = '';
    public ?string $building_details = null;
    public int $is_default = 0;
    public ?string $created_at = null;
}
