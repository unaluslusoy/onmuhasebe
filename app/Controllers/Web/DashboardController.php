<?php

namespace App\Controllers\Web;

class DashboardController
{
    /**
     * Display dashboard page
     */
    public function index()
    {
        // Check if user is logged in (WebAuthMiddleware already does this, but double check)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /giris');
            exit;
        }
        
        // Dashboard is a full HTML page, just include it directly
        require_once __DIR__ . '/../../Views/dashboard/index.php';
    }
}
