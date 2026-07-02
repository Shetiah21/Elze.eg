<?php

namespace App\Core;

use App\Config\Config;

abstract class Controller
{
    /**
     * Helper to render views
     */
    protected function render(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    /**
     * Helper to redirect to a path (e.g. '/login', '/dashboard')
     */
    protected function redirect(string $path): void
    {
        // Use the APP_BASE_PATH constant defined in index.php (strips to just the route segment)
        $base = defined('APP_BASE_PATH') ? APP_BASE_PATH : '';
        $redirectUrl = $base . '/' . ltrim($path, '/');

        header("Location: " . $redirectUrl);
        exit;
    }

    /**
     * Helper to return JSON responses (API-like endpoints)
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check if request method is POST
     */
    protected function isPost(): bool
    {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    /**
     * Retrieve post body content safely
     */
    protected function getPostData(): array
    {
        $data = [];
        
        // Form post variables
        foreach ($_POST as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            } else {
                $data[$key] = $value;
            }
        }
        
        // JSON body post variables
        $json = file_get_contents('php://input');
        if (!empty($json)) {
            $jsonData = json_decode($json, true);
            if (is_array($jsonData)) {
                $data = array_merge($data, $jsonData);
            }
        }

        return $data;
    }

    /**
     * Retrieve query params safely
     */
    protected function getQueryParams(): array
    {
        $data = [];
        foreach ($_GET as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }
}
