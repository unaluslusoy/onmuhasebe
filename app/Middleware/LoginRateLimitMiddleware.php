<?php

namespace App\Middleware;

use App\Helpers\Response;
use App\Helpers\Logger;

/**
 * Login Rate Limiting Middleware
 * Prevents brute force attacks on login endpoint
 */
class LoginRateLimitMiddleware
{
    private int $maxAttempts;
    private int $decayMinutes;
    private string $storagePath;

    public function __construct()
    {
        $this->maxAttempts = (int)($_ENV['LOGIN_MAX_ATTEMPTS'] ?? 5);
        $this->decayMinutes = (int)($_ENV['LOGIN_DECAY_MINUTES'] ?? 15);
        $this->storagePath = __DIR__ . '/../../storage/cache/login_attempts/';

        // Create storage directory
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Handle login rate limiting
     */
    public function handle(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "login_attempts:{$ip}";

        $attempts = $this->getAttempts($key);

        if ($attempts >= $this->maxAttempts) {
            $retryAfter = $this->getRetryAfter($key);

            Logger::security('Login blocked - too many attempts', [
                'ip' => $ip,
                'attempts' => $attempts,
                'max_attempts' => $this->maxAttempts,
                'retry_after' => $retryAfter
            ]);

            Response::error(
                "Too many login attempts. Please try again in {$this->decayMinutes} minutes.",
                429,
                [
                    'retry_after' => $retryAfter,
                    'blocked_until' => date('Y-m-d H:i:s', time() + $retryAfter)
                ]
            );
        }

        // Register shutdown function to increment on failure
        register_shutdown_function(function() use ($key, $ip) {
            $responseCode = http_response_code();

            // Increment only on failed login (401)
            if ($responseCode === 401) {
                $this->incrementAttempts($key);

                $attempts = $this->getAttempts($key);

                Logger::security('Failed login attempt recorded', [
                    'ip' => $ip,
                    'attempts' => $attempts,
                    'remaining' => max(0, $this->maxAttempts - $attempts)
                ]);

                // Warning when approaching limit
                if ($attempts === $this->maxAttempts - 1) {
                    Logger::security('Login attempts warning - one attempt remaining', [
                        'ip' => $ip
                    ]);
                }
            }

            // Clear attempts on successful login (200)
            if ($responseCode === 200) {
                $this->clearAttempts($key);

                Logger::security('Login attempts cleared after successful login', [
                    'ip' => $ip
                ]);
            }
        });
    }

    /**
     * Get current attempts count
     */
    private function getAttempts(string $key): int
    {
        $filename = $this->storagePath . md5($key) . '.cache';

        if (!file_exists($filename)) {
            return 0;
        }

        $data = json_decode(file_get_contents($filename), true);

        if (!$data || !isset($data['expires_at']) || $data['expires_at'] < time()) {
            @unlink($filename);
            return 0;
        }

        return $data['attempts'] ?? 0;
    }

    /**
     * Increment attempts counter
     */
    private function incrementAttempts(string $key): void
    {
        $filename = $this->storagePath . md5($key) . '.cache';
        $expiresAt = time() + ($this->decayMinutes * 60);

        $data = [
            'attempts' => $this->getAttempts($key) + 1,
            'expires_at' => $expiresAt,
            'first_attempt' => file_exists($filename)
                ? json_decode(file_get_contents($filename), true)['first_attempt'] ?? time()
                : time()
        ];

        file_put_contents($filename, json_encode($data), LOCK_EX);
    }

    /**
     * Clear attempts counter
     */
    private function clearAttempts(string $key): void
    {
        $filename = $this->storagePath . md5($key) . '.cache';

        if (file_exists($filename)) {
            @unlink($filename);
        }
    }

    /**
     * Get seconds until rate limit resets
     */
    private function getRetryAfter(string $key): int
    {
        $filename = $this->storagePath . md5($key) . '.cache';

        if (!file_exists($filename)) {
            return $this->decayMinutes * 60;
        }

        $data = json_decode(file_get_contents($filename), true);

        if (!$data || !isset($data['expires_at'])) {
            return $this->decayMinutes * 60;
        }

        return max(1, $data['expires_at'] - time());
    }

    /**
     * Get remaining attempts for IP
     */
    public static function getRemainingAttempts(string $ip = null): int
    {
        $middleware = new self();

        if ($ip === null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }

        $key = "login_attempts:{$ip}";
        $attempts = $middleware->getAttempts($key);

        return max(0, $middleware->maxAttempts - $attempts);
    }

    /**
     * Check if IP is blocked
     */
    public static function isBlocked(string $ip = null): bool
    {
        return self::getRemainingAttempts($ip) === 0;
    }

    /**
     * Clear login attempts for IP (admin function)
     */
    public static function clearIp(string $ip): void
    {
        $middleware = new self();
        $key = "login_attempts:{$ip}";
        $middleware->clearAttempts($key);

        Logger::security('Login attempts manually cleared', [
            'ip' => $ip,
            'cleared_by' => 'admin'
        ]);
    }
}
