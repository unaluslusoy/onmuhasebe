<?php

namespace App\Middleware;

use App\Helpers\Security;
use App\Helpers\Response;
use App\Helpers\Logger;

/**
 * CSRF Protection Middleware
 * Validates CSRF tokens on state-changing requests
 */
class CsrfMiddleware
{
    /**
     * Handle CSRF token validation
     */
    public function handle(): void
    {
        // Check if CSRF protection is enabled
        if (!($_ENV['CSRF_PROTECTION'] ?? true)) {
            return;
        }

        // Only check on state-changing methods
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        // Skip CSRF check for API routes (JWT is sufficient)
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($uri, '/api/') !== false) {
            return;
        }

        // Get token from request
        $token = $this->getTokenFromRequest();

        if (!$token) {
            Logger::security('CSRF token missing', [
                'method' => $method,
                'uri' => $uri,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            Response::error('CSRF token validation failed. Please refresh the page and try again.', 403);
        }

        // Verify token
        if (!Security::verifyCsrfToken($token)) {
            Logger::security('CSRF token validation failed', [
                'method' => $method,
                'uri' => $uri,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'token_provided' => substr($token, 0, 10) . '...'
            ]);

            Response::error('CSRF token validation failed. Please refresh the page and try again.', 403);
        }

        // Token valid - optionally regenerate for extra security
        if ($_ENV['CSRF_REGENERATE'] ?? false) {
            Security::regenerateCsrfToken();
        }
    }

    /**
     * Get CSRF token from request
     */
    private function getTokenFromRequest(): ?string
    {
        // 1. Check POST data
        if (isset($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // 2. Check JSON body
        $json = file_get_contents('php://input');
        if ($json) {
            $data = json_decode($json, true);
            if (isset($data['csrf_token'])) {
                return $data['csrf_token'];
            }
        }

        // 3. Check headers
        if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (isset($_SERVER['HTTP_X_XSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_XSRF_TOKEN'];
        }

        return null;
    }
}
