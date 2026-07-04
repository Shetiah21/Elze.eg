<?php

namespace App\Core;

use App\Repositories\UserRepository;

abstract class AdminController extends Controller
{
    protected Session $session;
    protected \PDO $db;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Require an authenticated admin user; otherwise 403 or redirect to login.
     */
    protected function requireAdmin(): array
    {
        $user = $this->session->get('user');

        if (!$user) {
            $this->session->setFlash('error', 'You must be logged in to access the admin panel.');
            $this->redirect('/login');
        }

        if (($user['role'] ?? '') !== 'admin') {
            $this->renderForbidden();
        }

        $repo = new UserRepository();
        $dbUser = $repo->findById((int) $user['id']);

        if (!$dbUser || $dbUser->status === 'blocked') {
            $this->session->remove('user');
            $this->session->setFlash('error', 'Your account has been suspended.');
            $this->redirect('/login');
        }

        return $user;
    }

    protected function validateCsrf(): void
    {
        $data = $this->getPostData();
        if (!$this->session->validateCsrfToken($data['csrf_token'] ?? null)) {
            $this->session->setFlash('error', 'CSRF validation failed. Please try again.');
            $this->redirect('/admin');
        }
    }

    protected function renderForbidden(): void
    {
        http_response_code(403);
        View::render('errors/403', [
            'title' => '403 Forbidden | Elze.eg',
        ], 'main');
        exit;
    }

    protected function renderAdmin(string $view, array $data = []): void
    {
        $data['csrf_token'] = $this->session->getCsrfToken();
        $data['admin_user'] = $this->session->get('user');
        $this->render($view, $data, 'admin');
    }

    protected function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-') ?: 'item';
    }
}
