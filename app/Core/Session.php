<?php

namespace App\Core;

class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
            if (!headers_sent()) {
                ini_set('session.cookie_httponly', 1);
                ini_set('session.use_only_cookies', 1);
            }
            session_start();
        }

        // Initialize flash data container
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
    }

    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy(): void
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Set a flash message that persists for only the next request
     */
    public function setFlash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = [
            'value' => $value,
            'keep' => true
        ];
    }

    /**
     * Get a flash message and mark it for deletion
     */
    public function getFlash(string $key, $default = null)
    {
        if (isset($_SESSION['_flash'][$key])) {
            $value = $_SESSION['_flash'][$key]['value'];
            // Mark for removal at the end of the request
            unset($_SESSION['_flash'][$key]);
            return $value;
        }
        return $default;
    }

    /**
     * Clean up flash messages that were not updated in this request
     */
    public function cleanFlashes(): void
    {
        if (isset($_SESSION['_flash'])) {
            foreach ($_SESSION['_flash'] as $key => $data) {
                if (!$data['keep']) {
                    unset($_SESSION['_flash'][$key]);
                } else {
                    $_SESSION['_flash'][$key]['keep'] = false; // Mark to clear next request
                }
            }
        }
    }

    /**
     * Validate that the current session user is still active (not blocked).
     * Terminates session immediately if the user was blocked by an admin.
     */
    public function validateActiveSession(): void
    {
        if (!$this->has('user')) {
            return;
        }

        $sessionUser = $this->get('user');
        $repo = new \App\Repositories\UserRepository();
        $dbUser = $repo->findById((int) $sessionUser['id']);

        if (!$dbUser || $dbUser->status === 'blocked') {
            $this->remove('user');
            $this->setFlash('error', 'Your account has been suspended. Please contact customer support.');
        }
    }

    /**
     * Generate or fetch a CSRF token for forms
     */
    public function getCsrfToken(): string
    {
        if (!$this->has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            $this->set('csrf_token', $token);
        }
        return $this->get('csrf_token');
    }

    /**
     * Validate form CSRF token
     */
    public function validateCsrfToken(?string $token): bool
    {
        if ($token === null) {
            return false;
        }
        $stored = $this->get('csrf_token');
        return $stored !== null && hash_equals($stored, $token);
    }

    // Prevent cloning and serialization
    private function __clone() {}
    public function __wakeup() {}
}
