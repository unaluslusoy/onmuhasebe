<?php

namespace App\Controllers\Web;

class PageController
{
    /**
     * Homepage - redirect to dashboard or login
     */
    public function home()
    {
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
        } else {
            header('Location: /login');
        }
        exit;
    }
    
    /**
     * Render view with layout
     */
    private function render(string $view, array $data = [], string $layout = 'metronic')
    {
        // Extract data to variables
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include view file
        $viewPath = __DIR__ . '/../../Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            die("View not found: {$view}");
        }
        
        // Get content
        $content = ob_get_clean();
        
        // Include layout
        $layoutPath = __DIR__ . '/../../Views/layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            include $layoutPath;
        } else {
            echo $content;
        }
    }
}
