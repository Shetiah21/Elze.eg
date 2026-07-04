<?php

namespace App\Controllers;

use App\Core\AdminController;
use App\Models\User;
use PDO;

class AdminUserController extends AdminController
{
    public function index(): void
    {
        $this->requireAdmin();
        $stmt = $this->db->query("
            SELECT id, name, email, role, status, email_verified_at, created_at
            FROM users
            ORDER BY created_at DESC
        ");

        $this->renderAdmin('admin/users/index', [
            'title' => 'Users | Admin',
            'active_section' => 'users',
            'users' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    public function toggleStatus(string $id): void
    {
        $this->requireAdmin();
        if ($this->isPost()) {
            $this->validateCsrf();
            $user = User::find((int) $id);
            $admin = $this->session->get('user');

            if (!$user) {
                $this->session->setFlash('error', 'User not found.');
                $this->redirect('/admin/users');
            }

            if ($user->role === 'admin') {
                $this->session->setFlash('error', 'Cannot block admin accounts.');
                $this->redirect('/admin/users');
            }

            if ((int) $admin['id'] === (int) $user->id) {
                $this->session->setFlash('error', 'You cannot block your own account.');
                $this->redirect('/admin/users');
            }

            $user->status = $user->status === 'active' ? 'blocked' : 'active';
            $user->remember_token = null;
            $user->save();

            $action = $user->status === 'blocked' ? 'blocked' : 'unblocked';
            $this->session->setFlash('success', "User {$action} successfully.");
        }
        $this->redirect('/admin/users');
    }
}
