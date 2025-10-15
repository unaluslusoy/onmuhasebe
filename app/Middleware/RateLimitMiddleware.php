<?php

namespace App\Middleware;

use App\Helpers\Response;
use App\Helpers\Logger;

/**
 * Rate Limiting Middleware
 * Prevents abuse by limiting requests per IP address
 */
class RateLimitMiddleware
{
    private int $maxRequests;
    private int $decayMinutes;
    private string $storageType;
    private string $storagePath;

    public function __construct()
    {
        $this->maxRequests = (int)($_ENV['RATE_LIMIT_REQUESTS'] ?? 60);
        $this->decayMinutes = (int)($_ENV['RATE_LIMIT_PERIOD'] ?? 60) / 60; // Convert seconds to minutes
        $this->storageType = $_ENV['RATE_LIMIT_STORAGE'] ?? 'file'; // 'file' or 'redis'
        $this->storagePath = __DIR__ . '/../../storage/cache/rate_limits/';

        // Create storage directory
        if ($this->storageType === 'file' && !is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    /**
     * Handle rate limiting
     */
    public function handle(): void
    {
        // Check if rate limiting is enabled
        if (!($_ENV['RATE_LIMIT_ENABLED'] ?? true)) {
            return;
        }

        $identifier = $this->getIdentifier();
        $key = "rate_limit:{$identifier}";

        $attempts = $this->getAttempts($key);
        $maxAttempts = $this->maxRequests;

        if ($attempts >= $maxAttempts) {
            $retryAfter = $this->getRetryAfter($key);

            Logger::security('Rate limit exceeded', [
                'ip' => $identifier,
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
                'retry_after' => $retryAfter
            ]);

            // Set retry-after header
            header("Retry-After: {$retryAfter}");
            header('X-RateLimit-Limit: ' . $maxAttempts);
            header('X-RateLimit-Remaining: 0');
            header('X-RateLimit-Reset: ' . (time() + $retryAfter));

            Response::error(
                'Too many requests. Please try again later.',
                429,
                [
                    'retry_after' => $retryAfter,
                    'retry_after_human' => $this->secondsToHuman($retryAfter)
                ]
            );
        }

        // Increment attempts
        $this->incrementAttempts($key);

        // Set rate limit headers
        $remaining = $maxAttempts - ($attempts + 1);
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: ' . max(0, $remaining));
        header('X-RateLimit-Reset: ' . (time() + ($this->decayMinutes * 60)));
    }

    /**
     * Get unique identifier for rate limiting
     */
    private function getIdentifier(): string
    {
        // Use IP + User Agent for better uniqueness
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        return md5($ip . '|' . $userAgent);
    }

    /**
     * Get current attempts count
     */
    private function getAttempts(string $key): int
    {
        if ($this->storageType === 'redis') {
            return $this->getAttemptsRedis($key);
        }

        return $this->getAttemptsFile($key);
    }

    /**
     * Get attempts from file storage
     */
    private function getAttemptsFile(string $key): int
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
     * Get attempts from Redis
     */
    private function getAttemptsRedis(string $key): int
    {
        try {
            $redis = new \Predis\Client([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
            ]);

            $value = $redis->get($key);
            return $value ? (int)$value : 0;

        } catch (\Exception $e) {
            Logger::warning('Redis connection failed, falling back to file storage', [
                'error' => $e->getMessage()
            ]);
            return $this->getAttemptsFile($key);
        }
    }

    /**
     * Increment attempts counter
     */
    private function incrementAttempts(string $key): void
    {
        if ($this->storageType === 'redis') {
            $this->incrementAttemptsRedis($key);
        } else {
            $this->incrementAttemptsFile($key);
        }
    }

    /**
     * Increment attempts in file storage
     */
    private function incrementAttemptsFile(string $key): void
    {
        $filename = $this->storagePath . md5($key) . '.cache';
        $expiresAt = time() + ($this->decayMinutes * 60);

        $data = [
            'attempts' => $this->getAttemptsFile($key) + 1,
            'expires_at' => $expiresAt
        ];

        file_put_contents($filename, json_encode($data), LOCK_EX);
    }

    /**
     * Increment attempts in Redis
     */
    private function incrementAttemptsRedis(string $key): void
    {
        try {
            $redis = new \Predis\Client([
                'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                'port' => $_ENV['REDIS_PORT'] ?? 6379,
            ]);

            $ttl = $this->decayMinutes * 60;

            // Increment and set expiry
            $redis->incr($key);
            $redis->expire($key, $ttl);

        } catch (\Exception $e) {
            Logger::warning('Redis increment failed, using file storage', [
                'error' => $e->getMessage()
            ]);
            $this->incrementAttemptsFile($key);
        }
    }

    /**
     * Get seconds until rate limit resets
     */
    private function getRetryAfter(string $key): int
    {
        if ($this->storageType === 'redis') {
            try {
                $redis = new \Predis\Client([
                    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                    'port' => $_ENV['REDIS_PORT'] ?? 6379,
                ]);

                $ttl = $redis->ttl($key);
                return max(1, $ttl);

            } catch (\Exception $e) {
                // Fallback
            }
        }

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
     * Convert seconds to human readable format
     */
    private function secondsToHuman(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' second' . ($seconds !== 1 ? 's' : '');
        }

        $minutes = floor($seconds / 60);
        return $minutes . ' minute' . ($minutes !== 1 ? 's' : '');
    }

    /**
     * Clear rate limit for identifier (useful for testing)
     */
    public static function clear(string $identifier = null): void
    {
        $middleware = new self();

        if ($identifier === null) {
            $identifier = $middleware->getIdentifier();
        }

        $key = "rate_limit:{$identifier}";

        if ($middleware->storageType === 'redis') {
            try {
                $redis = new \Predis\Client([
                    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                    'port' => $_ENV['REDIS_PORT'] ?? 6379,
                ]);

                $redis->del($key);
            } catch (\Exception $e) {
                // Ignore
            }
        }

        $filename = $middleware->storagePath . md5($key) . '.cache';
        if (file_exists($filename)) {
            @unlink($filename);
        }
    }
}
