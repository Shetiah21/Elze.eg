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
     * Helper to redirect to a path
     */
    protected function redirect(string $path): void
    {
        $url = Config::getInstance()->get('app.url', 'http://localhost/Elze.eg');
        
        // Remove duplicate slashes if redirect path contains leading slash
        $redirectUrl = rtrim($url, '/') . '/' . ltrim($path, '/');
        
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
