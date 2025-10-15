<?php

namespace App\Middleware;

use App\Services\Auth\JWTService;
use App\Models\User;
use App\Helpers\Response;

/**
 * Authentication Middleware
 * Validates JWT token for protected routes
 */
class AuthMiddleware
{
    private JWTService $jwtService;
    private User $userModel;

    public function __construct()
    {
        $this->jwtService = new JWTService();
        $this->userModel = new User();
    }

    /**
     * Handle authentication check
     * 
     * @param callable $next
     * @return mixed
     */
    public function handle($next = null)
    {
        $token = $this->jwtService->getTokenFromHeader();

        if (!$token) {
            Response::unauthorized('No authentication token provided');
        }

        $tokenData = $this->jwtService->getUserFromToken($token);

        if (!$tokenData) {
            Response::unauthorized('Invalid or expired token');
        }

        // Verify token type is 'access'
        $decoded = $this->jwtService->verifyToken($token);
        if ($decoded && isset($decoded->type) && $decoded->type !== 'access') {
            Response::unauthorized('Invalid token type');
        }

        // Get full user data from database
        $user = $this->userModel->find($tokenData['user_id']);
        
        if (!$user || !$user['is_active']) {
            Response::unauthorized('User account is inactive or not found');
        }

        // Store user data in request for controllers
        $_REQUEST['auth_user'] = $user;
        
        // Also store in GLOBALS for backward compatibility
        $GLOBALS['auth_user'] = $user;
        
        // Continue to next middleware or controller
        if ($next && is_callable($next)) {
            return $next();
        }
    }
}
