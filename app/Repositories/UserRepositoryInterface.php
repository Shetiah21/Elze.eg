<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    
    public function findByEmail(string $email): ?User;
    
    public function findByRememberToken(string $token): ?User;
    
    public function save(User $user): bool;
    
    public function delete(int $id): bool;
}
