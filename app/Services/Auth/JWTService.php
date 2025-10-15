<?php

namespace App\Services\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

/**
 * JWT Service
 * Handles JWT token generation, verification, and management
 */
class JWTService
{
    private string $secret;
    private string $algorithm;
    private int $accessExpiry;
    private int $refreshExpiry;

    public function __construct()
    {
        $this->secret = config('jwt.secret');
        $this->algorithm = config('jwt.algorithm');
        $this->accessExpiry = (int) config('jwt.access_expiry');
        $this->refreshExpiry = (int) config('jwt.refresh_expiry');
    }

    /**
     * Generate access token
     */
    public function generateAccessToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->accessExpiry;

        $tokenPayload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'type' => 'access',
            'data' => $payload
        ];

        return JWT::encode($tokenPayload, $this->secret, $this->algorithm);
    }

    /**
     * Generate refresh token
     */
    public function generateRefreshToken(array $payload): string
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->refreshExpiry;

        $tokenPayload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'type' => 'refresh',
            'data' => $payload
        ];

        return JWT::encode($tokenPayload, $this->secret, $this->algorithm);
    }

    /**
     * Verify and decode token
     */
    public function verifyToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            
            // Check if token is expired
            if (isset($decoded->exp) && $decoded->exp < time()) {
                logger('Token expired', 'warning', ['exp' => $decoded->exp]);
                return null;
            }

            return $decoded;
        } catch (Exception $e) {
            logger('Token verification failed: ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get token from Authorization header
     */
    public function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authHeader = $headers['Authorization'];
        
        // Bearer token format: "Bearer <token>"
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract user data from token
     */
    public function getUserFromToken(?string $token = null): ?array
    {
        if ($token === null) {
            $token = $this->getTokenFromHeader();
        }

        if ($token === null) {
            return null;
        }

        $decoded = $this->verifyToken($token);

        if ($decoded === null || !isset($decoded->data)) {
            return null;
        }

        return (array) $decoded->data;
    }

    /**
     * Check if token is valid
     */
    public function isValid(string $token): bool
    {
        return $this->verifyToken($token) !== null;
    }

    /**
     * Get token expiry time
     */
    public function getExpiry(string $token): ?int
    {
        $decoded = $this->verifyToken($token);
        return $decoded->exp ?? null;
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        $decoded = $this->verifyToken($refreshToken);

        if ($decoded === null) {
            return null;
        }

        // Verify it's a refresh token
        if (!isset($decoded->type) || $decoded->type !== 'refresh') {
            logger('Invalid token type for refresh', 'warning');
            return null;
        }

        $payload = (array) $decoded->data;
        $newAccessToken = $this->generateAccessToken($payload);

        return [
            'access_token' => $newAccessToken,
            'expires_in' => $this->accessExpiry
        ];
    }

    /**
     * Generate both access and refresh tokens
     */
    public function generateTokenPair(array $payload): array
    {
        return [
            'access_token' => $this->generateAccessToken($payload),
            'refresh_token' => $this->generateRefreshToken($payload),
            'token_type' => 'Bearer',
            'expires_in' => $this->accessExpiry
        ];
    }
}
