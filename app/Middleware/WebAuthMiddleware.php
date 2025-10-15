<?php

namespace App\Middleware;

use App\Helpers\Response;

/**
 * Web Authentication Middleware
 * Checks if user is authenticated via session
 */
class WebAuthMiddleware
{
    /**
     * Handle the request
     * 
     * @param callable $next
     * @return mixed
     */
    public function handle($next)
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Check if user is authenticated (support both session structures)
        $isAuthenticated = false;
        
        // Check new session structure (from createSession)
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $isAuthenticated = true;
            // Also set user_id for backward compatibility
            $_SESSION['user_id'] = $_SESSION['user']['id'];
            $_SESSION['company_id'] = $_SESSION['user']['company_id'] ?? null;
        }
        // Check old session structure (legacy)
        elseif (isset($_SESSION['user_id'])) {
            $isAuthenticated = true;
        }
        
        if (!$isAuthenticated) {
            // Redirect to login page
            header('Location: /giris');
            exit;
        }
        
        // Check if session is expired (optional - 8 hours)
        if (isset($_SESSION['last_activity'])) {
            $sessionLifetime = 28800; // 8 hours in seconds
            if (time() - $_SESSION['last_activity'] > $sessionLifetime) {
                // Session expired
                session_unset();
                session_destroy();
                header('Location: /giris?expired=1');
                exit;
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
        
        // Continue to next middleware or controller
        return $next();
    }
}
