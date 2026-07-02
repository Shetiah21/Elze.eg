<?php

namespace App\Core;

use Exception;

class Router
{
    private array $routes = [];

    /**
     * Add a GET route
     */
    public function get(string $route, $handler): void
    {
        $this->addRoute('GET', $route, $handler);
    }

    /**
     * Add a POST route
     */
    public function post(string $route, $handler): void
    {
        $this->addRoute('POST', $route, $handler);
    }

    /**
     * Internal helper to register routes
     */
    private function addRoute(string $method, string $route, $handler): void
    {
        // Convert simple route placeholders like {slug} or {id} into regex patterns
        // e.g. /products/{slug} => ^/products/(?P<slug>[^/]+)$
        $routePattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
        $routePattern = '#^' . $routePattern . '$#';

        $this->routes[$method][$routePattern] = $handler;
    }

    /**
     * Resolve the request and execute the handler
     */
    public function resolve(string $requestUri, string $requestMethod)
    {
        // Strip query parameters
        $path = parse_url($requestUri, PHP_URL_PATH);
        
        // Strip base directory /Elze.eg/public if running in subfolders
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);
        
        // Ensure base directory normalization
        if ($baseDir !== '/' && strpos($path, $baseDir) === 0) {
            $path = substr($path, strlen($baseDir));
        }
        
        $path = '/' . trim($path, '/');
        $method = strtoupper($requestMethod);

        if (!isset($this->routes[$method])) {
            $this->sendNotFound();
            return;
        }

        foreach ($this->routes[$method] as $pattern => $handler) {
            if (preg_match($pattern, $path, $matches)) {
                // Filter out non-string keys from named capture groups
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                return $this->executeHandler($handler, $params);
            }
        }

        $this->sendNotFound();
    }

    /**
     * Dispatch execution to controller and method
     */
    private function executeHandler($handler, array $params = [])
    {
        if (is_array($handler)) {
            [$controllerClass, $method] = $handler;

            if (class_exists($controllerClass)) {
                // Simple dependency injection container resolution
                // Instantiate the controller
                $controller = new $controllerClass();
                
                if (method_exists($controller, $method)) {
                    return call_user_func_array([$controller, $method], $params);
                }
                
                throw new Exception("Method '{$method}' not found in Controller '{$controllerClass}'");
            }
            
            throw new Exception("Controller Class '{$controllerClass}' not found");
        }

        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }

        throw new Exception("Invalid route handler defined");
    }

    /**
     * Render a standard 404 response
     */
    private function sendNotFound(): void
    {
        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
        echo "<p>The requested URL was not found on this server.</p>";
    }
}
