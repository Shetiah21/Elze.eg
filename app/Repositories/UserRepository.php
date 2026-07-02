<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetch a user by ID
     */
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    /**
     * Fetch a user by Email address
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        
        return $row ? User::instantiate($row) : null;
    }

    /**
     * Fetch a user by Remember Me Token
     */
    public function findByRememberToken(string $token): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE remember_token = :token LIMIT 1");
        $stmt->execute(['token' => $token]);
        $row = $stmt->fetch();
        
        return $row ? User::instantiate($row) : null;
    }

    /**
     * Insert or update a user model
     */
    public function save(User $user): bool
    {
        return $user->save();
    }

    /**
     * Delete a user by ID
     */
    public function delete(int $id): bool
    {
        $user = $this->findById($id);
        if ($user) {
            return $user->delete();
        }
        return false;
    }
}
