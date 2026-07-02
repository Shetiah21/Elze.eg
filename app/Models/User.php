<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public ?int $id = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'user';
    public string $status = 'active';
    public ?string $email_verified_at = null;
    public ?string $otp_code = null;
    public ?string $otp_expires_at = null;
    public ?string $remember_token = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;
}
