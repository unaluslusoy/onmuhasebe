<?php

namespace App\Helpers;

/**
 * Security Helper Class
 * Provides CSRF protection, XSS sanitization, and other security utilities
 */
class Security
{
    /**
     * Generate CSRF token and store in session
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get CSRF token from session
     */
    public static function getCsrfToken(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return $_SESSION['csrf_token'] ?? null;
    }

    /**
     * Regenerate CSRF token (use after successful form submission)
     */
    public static function regenerateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return $_SESSION['csrf_token'];
    }

    /**
     * Clean XSS from input (single value)
     */
    public static function xssClean($data)
    {
        if (is_array($data)) {
            return self::xssCleanArray($data);
        }

        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return $data;
    }

    /**
     * Clean XSS from array recursively
     */
    public static function xssCleanArray(array $data): array
    {
        $cleaned = [];

        foreach ($data as $key => $value) {
            $cleanKey = htmlspecialchars($key, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            if (is_array($value)) {
                $cleaned[$cleanKey] = self::xssCleanArray($value);
            } elseif (is_string($value)) {
                $cleaned[$cleanKey] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } else {
                $cleaned[$cleanKey] = $value;
            }
        }

        return $cleaned;
    }

    /**
     * Strip tags and clean HTML
     */
    public static function stripTags(string $data, string $allowedTags = ''): string
    {
        return strip_tags($data, $allowedTags);
    }

    /**
     * Clean SQL input (additional layer - PDO already protects)
     */
    public static function sqlClean(string $data): string
    {
        // Remove common SQL injection patterns
        $dangerous = ['--', ';--', ';', '/*', '*/', 'xp_', 'sp_', 'exec', 'execute', 'union', 'select', 'insert', 'update', 'delete', 'drop', 'create', 'alter'];

        $data = str_ireplace($dangerous, '', $data);

        return trim($data);
    }

    /**
     * Generate secure random string
     */
    public static function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Generate secure random number
     */
    public static function generateRandomNumber(int $min = 100000, int $max = 999999): int
    {
        return random_int($min, $max);
    }

    /**
     * Hash password securely (Argon2)
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehash
     */
    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove any path components
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        return $filename;
    }

    /**
     * Validate email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     */
    public static function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate IP address
     */
    public static function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Encrypt data (AES-256-CBC)
     */
    public static function encrypt(string $data, string $key = null): string
    {
        $key = $key ?? $_ENV['JWT_SECRET'] ?? 'default-encryption-key';
        $key = hash('sha256', $key, true);

        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt data
     */
    public static function decrypt(string $data, string $key = null): string
    {
        $key = $key ?? $_ENV['JWT_SECRET'] ?? 'default-encryption-key';
        $key = hash('sha256', $key, true);

        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Mask sensitive data (for display purposes)
     */
    public static function maskData(string $data, int $visibleChars = 4): string
    {
        $length = strlen($data);

        if ($length <= $visibleChars) {
            return str_repeat('*', $length);
        }

        $visible = substr($data, -$visibleChars);
        $masked = str_repeat('*', $length - $visibleChars);

        return $masked . $visible;
    }

    /**
     * Generate secure API key
     */
    public static function generateApiKey(): string
    {
        return 'sk_' . bin2hex(random_bytes(32));
    }

    /**
     * Validate Turkish TC Kimlik No
     */
    public static function isValidTCKN(string $tckn): bool
    {
        if (strlen($tckn) !== 11 || !ctype_digit($tckn) || $tckn[0] === '0') {
            return false;
        }

        $digits = str_split($tckn);

        // First 10 digits sum
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$digits[$i];
        }

        if ($sum % 10 !== (int)$digits[10]) {
            return false;
        }

        // Odd and even position check
        $oddSum = 0;
        $evenSum = 0;

        for ($i = 0; $i < 9; $i++) {
            if ($i % 2 === 0) {
                $oddSum += (int)$digits[$i];
            } else {
                $evenSum += (int)$digits[$i];
            }
        }

        $check = (($oddSum * 7) - $evenSum) % 10;

        return $check === (int)$digits[9];
    }

    /**
     * Validate Turkish VKN (Vergi Kimlik No)
     */
    public static function isValidVKN(string $vkn): bool
    {
        if (strlen($vkn) !== 10 || !ctype_digit($vkn)) {
            return false;
        }

        $v = [];
        for ($i = 0; $i < 10; $i++) {
            $v[$i] = (int)$vkn[$i];
        }

        $lastDigit = $v[9];

        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $tmp = ($v[$i] + (9 - $i)) % 10;
            $tmp = ($tmp * (2 ** (9 - $i))) % 9;

            if ($tmp === 0) {
                $tmp = 9;
            }

            $sum += $tmp;
        }

        $calculatedLastDigit = (10 - ($sum % 10)) % 10;

        return $lastDigit === $calculatedLastDigit;
    }

    /**
     * Rate limit check (simple file-based)
     */
    public static function checkRateLimit(string $identifier, int $maxAttempts = 5, int $decayMinutes = 15): bool
    {
        $cacheDir = __DIR__ . '/../../storage/cache/security/';

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $filename = $cacheDir . md5($identifier) . '.rate';

        if (!file_exists($filename)) {
            file_put_contents($filename, json_encode([
                'attempts' => 1,
                'expires_at' => time() + ($decayMinutes * 60)
            ]));
            return true;
        }

        $data = json_decode(file_get_contents($filename), true);

        if ($data['expires_at'] < time()) {
            file_put_contents($filename, json_encode([
                'attempts' => 1,
                'expires_at' => time() + ($decayMinutes * 60)
            ]));
            return true;
        }

        if ($data['attempts'] >= $maxAttempts) {
            return false;
        }

        $data['attempts']++;
        file_put_contents($filename, json_encode($data));

        return true;
    }

    /**
     * Clear rate limit
     */
    public static function clearRateLimit(string $identifier): void
    {
        $cacheDir = __DIR__ . '/../../storage/cache/security/';
        $filename = $cacheDir . md5($identifier) . '.rate';

        if (file_exists($filename)) {
            @unlink($filename);
        }
    }
}
