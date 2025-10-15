<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Models\RefreshToken;
use App\Models\Company;
use App\Models\Subscription;
use App\Services\Auth\JWTService;
use App\Helpers\Response;

/**
 * Authentication Controller
 * Handles user registration, login, logout, token refresh
 */
class AuthController
{
    private User $userModel;
    private RefreshToken $refreshTokenModel;
    private Company $companyModel;
    private Subscription $subscriptionModel;
    private JWTService $jwtService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->refreshTokenModel = new RefreshToken();
        $this->companyModel = new Company();
        $this->subscriptionModel = new Subscription();
        $this->jwtService = new JWTService();
    }

    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register(array $params = []): void
    {
        $input = $this->getJsonInput();

        // Validation
        $errors = $this->validateRegistration($input);
        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Check if email already exists
        if ($this->userModel->emailExists($input['email'])) {
            Response::error('Email already registered', 422);
        }

        // Create user, company, and trial subscription
        try {
            // Start transaction
            $this->userModel->beginTransaction();

            // 1. Create user first (without company_id)
            $userId = $this->userModel->createUser([
                'email' => sanitize($input['email']),
                'password' => $input['password'],
                'full_name' => sanitize($input['full_name']),
                'phone' => isset($input['phone']) ? sanitize($input['phone']) : null,
                'role' => 'admin', // First user of company is admin
                'is_active' => true
            ]);

            if (!$userId) {
                $this->userModel->rollback();
                Response::serverError('Failed to create user');
            }

            // 2. Create company with owner_id
            $companyId = $this->companyModel->create([
                'owner_id' => $userId,
                'name' => sanitize($input['company_name'] ?? $input['full_name'] . ' Company'),
                'email' => sanitize($input['email']),
                'phone' => isset($input['phone']) ? sanitize($input['phone']) : null,
                'country' => 'Turkey'
            ]);

            if (!$companyId) {
                $this->userModel->rollback();
                Response::serverError('Failed to create company');
            }

            // 3. Update user with company_id
            $updateSuccess = $this->userModel->update($userId, ['company_id' => $companyId]);

            if (!$updateSuccess) {
                $this->userModel->rollback();
                Response::serverError('Failed to link user to company');
            }

            // 4. Create 30-day trial subscription
            $subscriptionId = $this->subscriptionModel->createTrial($companyId);

            if (!$subscriptionId) {
                $this->userModel->rollback();
                Response::serverError('Failed to create trial subscription');
            }

            // Commit transaction
            $this->userModel->commit();

            // Get created user
            $user = $this->userModel->find($userId);
            $company = $this->companyModel->find($companyId);
            $subscription = $this->subscriptionModel->getByCompanyId($companyId);

            Response::created([
                'user' => $user,
                'company' => $company,
                'subscription' => [
                    'status' => $subscription['status'],
                    'trial_ends_at' => $subscription['trial_ends_at'],
                    'days_remaining' => $this->subscriptionModel->getDaysRemaining($companyId)
                ],
                'message' => 'Registration successful! 30-day free trial activated. Please login.'
            ], 'User registered successfully with 30-day trial');

        } catch (\Exception $e) {
            $this->userModel->rollback();
            logger('Registration failed: ' . $e->getMessage(), 'error');
            Response::serverError('Registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(array $params = []): void
    {
        $input = $this->getJsonInput();

        // Validation
        if (empty($input['email']) || empty($input['password'])) {
            Response::validationError([
                'email' => 'Email is required',
                'password' => 'Password is required'
            ]);
        }

        // Find user with password
        $user = $this->userModel->findForAuth($input['email']);

        if (!$user) {
            Response::unauthorized('Invalid credentials');
        }

        // Verify password
        if (!$this->userModel->verifyPassword($input['password'], $user['password'])) {
            Response::unauthorized('Invalid credentials');
        }

        // Check if user is active
        if (!$user['is_active']) {
            Response::forbidden('Account is disabled');
        }

        // Update last login
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->userModel->updateLastLogin($user['id'], $clientIp);

        // Generate tokens
        $tokenPayload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        $tokens = $this->jwtService->generateTokenPair($tokenPayload);

        // Store refresh token
        $this->refreshTokenModel->store(
            $user['id'],
            $tokens['refresh_token'],
            config('jwt.refresh_expiry')
        );

        // Remove password from response
        unset($user['password']);

        Response::success([
            'user' => $user,
            'tokens' => $tokens
        ], 'Login successful');
    }

    /**
     * Get authenticated user info
     * GET /api/auth/me
     */
    public function me(array $params = []): void
    {
        $userData = $this->jwtService->getUserFromToken();

        if (!$userData) {
            Response::unauthorized('Invalid token');
        }

        $user = $this->userModel->find($userData['user_id']);

        if (!$user) {
            Response::notFound('User not found');
        }

        Response::success($user);
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout(array $params = []): void
    {
        $token = $this->jwtService->getTokenFromHeader();

        if (!$token) {
            Response::unauthorized('No token provided');
        }

        $userData = $this->jwtService->getUserFromToken($token);

        if (!$userData) {
            Response::unauthorized('Invalid token');
        }

        // Revoke all user tokens
        $this->refreshTokenModel->revokeAllUserTokens($userData['user_id']);

        Response::success(null, 'Logged out successfully');
    }

    /**
     * Refresh access token
     * POST /api/auth/refresh
     */
    public function refresh(array $params = []): void
    {
        $input = $this->getJsonInput();

        if (empty($input['refresh_token'])) {
            Response::validationError(['refresh_token' => 'Refresh token is required']);
        }

        // Verify refresh token exists in database
        $tokenRecord = $this->refreshTokenModel->findValidToken($input['refresh_token']);

        if (!$tokenRecord) {
            Response::unauthorized('Invalid or expired refresh token');
        }

        // Generate new access token
        $newTokens = $this->jwtService->refreshAccessToken($input['refresh_token']);

        if (!$newTokens) {
            Response::unauthorized('Failed to refresh token');
        }

        Response::success($newTokens, 'Token refreshed successfully');
    }

    /**
     * Change password
     * POST /api/auth/change-password
     */
    public function changePassword(array $params = []): void
    {
        $userData = $this->jwtService->getUserFromToken();

        if (!$userData) {
            Response::unauthorized('Invalid token');
        }

        $input = $this->getJsonInput();

        // Validation
        $errors = [];
        if (empty($input['current_password'])) {
            $errors['current_password'] = 'Current password is required';
        }
        if (empty($input['new_password'])) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($input['new_password']) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters';
        }
        if (empty($input['new_password_confirmation'])) {
            $errors['new_password_confirmation'] = 'Password confirmation is required';
        } elseif ($input['new_password'] !== $input['new_password_confirmation']) {
            $errors['new_password_confirmation'] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            Response::validationError($errors);
        }

        // Get user with password
        $user = $this->userModel->findForAuth($userData['email']);

        if (!$user) {
            Response::notFound('User not found');
        }

        // Verify current password
        if (!$this->userModel->verifyPassword($input['current_password'], $user['password'])) {
            Response::error('Current password is incorrect', 422);
        }

        // Update password
        $success = $this->userModel->changePassword($user['id'], $input['new_password']);

        if (!$success) {
            Response::serverError('Failed to change password');
        }

        // Revoke all tokens (force re-login)
        $this->refreshTokenModel->revokeAllUserTokens($user['id']);

        Response::success(null, 'Password changed successfully. Please login again.');
    }

    /**
     * Create session for authenticated user
     * POST /api/auth/create-session
     */
    public function createSession(array $params = []): void
    {
        // This endpoint is called from login page to create PHP session
        // JWT validation is done by AuthMiddleware
        
        // Get user from request (set by AuthMiddleware)
        $user = $_REQUEST['auth_user'] ?? null;
        
        if (!$user) {
            Response::unauthorized('User not authenticated');
        }
        
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Store user in session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['full_name'] ?? $user['email'],
            'role' => $user['role'],
            'is_super_admin' => $user['is_super_admin'] ?? 0,
            'company_id' => $user['company_id'] ?? null
        ];
        $_SESSION['last_activity'] = time();
        
        Response::success([
            'message' => 'Session created successfully'
        ]);
    }

    /**
     * Validate registration input
     */
    private function validateRegistration(array $input): array
    {
        $errors = [];

        if (empty($input['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($input['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($input['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (empty($input['password_confirmation'])) {
            $errors['password_confirmation'] = 'Password confirmation is required';
        } elseif ($input['password'] !== $input['password_confirmation']) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }

        if (empty($input['full_name'])) {
            $errors['full_name'] = 'Full name is required';
        }

        // Company name is optional - if not provided, will use full_name + " Company"
        if (!empty($input['company_name']) && strlen($input['company_name']) < 2) {
            $errors['company_name'] = 'Company name must be at least 2 characters';
        }

        return $errors;
    }

    /**
     * Get JSON input from request body
     */
    private function getJsonInput(): array
    {
        $input = json_decode(file_get_contents('php://input'), true);
        return $input ?? [];
    }
}
