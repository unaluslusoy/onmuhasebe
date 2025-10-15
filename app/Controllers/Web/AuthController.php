<?php

namespace App\Controllers\Web;

class AuthController
{
    /**
     * Display login page
     */
    public function loginPage()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $this->render('auth/login', [
            'title' => 'Giriş Yap - Ön Muhasebe Sistemi'
        ], 'auth');
    }
    
    /**
     * Display register page
     */
    public function registerPage()
    {
        // If already logged in, redirect to dashboard
        if (isset($_SESSION['user'])) {
            header('Location: /dashboard');
            exit;
        }
        
        $this->render('auth/register', [
            'title' => 'Kayıt Ol - Ön Muhasebe Sistemi'
        ], 'auth');
    }
    
    /**
     * Logout user and redirect to login
     */
    public function logout()
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Redirect to login
        header('Location: /giris?logout=1');
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
