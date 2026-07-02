<?php

namespace App\Core;

use Exception;

class View
{
    /**
     * Render a view file with data, wrapped inside a layout
     */
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extract variables to local scope
        extract($data);

        // Define files paths
        $baseDir = dirname(__DIR__);
        $viewFile = $baseDir . '/Views/' . $view . '.php';
        $layoutFile = $baseDir . '/Views/layouts/' . $layout . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file '{$view}' not found at: {$viewFile}");
        }

        // 1. Capture the page-specific content inside a buffer
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // 2. Render the layout wrapper
        if ($layout && file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            // Fallback: output content directly if layout is false or doesn't exist
            echo $content;
        }
        
        // Clean session flash values after rendering is complete
        Session::getInstance()->cleanFlashes();
    }
}
