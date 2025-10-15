<?php

/**
 * Helper Functions
 * Global utility functions available throughout the application
 */

if (!function_exists('env')) {
    /**
     * Get environment variable with optional default
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $default;
        
        if ($value === null) {
            return $default;
        }

        // Convert string booleans
        return match (strtolower($value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value
        };
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = [];
        
        if (empty($config)) {
            $config = [
                'app' => [
                    'name' => env('APP_NAME', 'Ön Muhasebe Sistemi'),
                    'env' => env('APP_ENV', 'production'),
                    'debug' => env('APP_DEBUG', false),
                    'url' => env('APP_URL', 'http://localhost'),
                    'timezone' => env('APP_TIMEZONE', 'Europe/Istanbul'),
                ],
                'database' => [
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', 3306),
                    'database' => env('DB_DATABASE', 'onmuhasebe'),
                    'username' => env('DB_USERNAME', 'root'),
                    'password' => env('DB_PASSWORD', ''),
                    'charset' => env('DB_CHARSET', 'utf8mb4'),
                    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
                ],
                'jwt' => [
                    'secret' => env('JWT_SECRET', 'your-secret-key'),
                    'access_expiry' => env('JWT_ACCESS_EXPIRY', 3600),
                    'refresh_expiry' => env('JWT_REFRESH_EXPIRY', 2592000),
                    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
                ],
            ];
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the base path of the application
     */
    function base_path(string $path = ''): string
    {
        return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the app directory path
     */
    function app_path(string $path = ''): string
    {
        return base_path('app') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the public directory path
     */
    function public_path(string $path = ''): string
    {
        return base_path('public') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the storage directory path
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage') . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}

if (!function_exists('url')) {
    /**
     * Generate a URL for the application
     */
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.url'), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset URL
     */
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die
     */
    function dd(...$vars): never
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die(1);
    }
}

if (!function_exists('logger')) {
    /**
     * Log a message
     */
    function logger(string $message, string $level = 'info', array $context = []): void
    {
        $logPath = storage_path('logs');
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }

        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $logFile = $logPath . "/app-{$date}.log";
        
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$time}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

if (!function_exists('sanitize')) {
    /**
     * Sanitize input data
     */
    function sanitize(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map('sanitize', $data);
        }
        
        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        
        return $data;
    }
}

if (!function_exists('old')) {
    /**
     * Get old input value
     */
    function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION['_old_input'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get CSRF token
     */
    function csrf_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate CSRF input field
     */
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format number as Turkish Lira
     */
    function format_currency(float $amount, string $currency = 'TRY'): string
    {
        $formatted = number_format($amount, 2, ',', '.');
        
        return match ($currency) {
            'TRY' => $formatted . ' ₺',
            'USD' => '$' . $formatted,
            'EUR' => '€' . $formatted,
            default => $formatted . ' ' . $currency
        };
    }
}

if (!function_exists('format_date')) {
    /**
     * Format date to Turkish format
     */
    function format_date(?string $date, string $format = 'd.m.Y'): string
    {
        if (!$date) {
            return '';
        }
        
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '';
    }
}

if (!function_exists('vkn_validate')) {
    /**
     * Validate Turkish Tax ID (VKN)
     */
    function vkn_validate(string $vkn): bool
    {
        $vkn = preg_replace('/[^0-9]/', '', $vkn);
        
        if (strlen($vkn) !== 10) {
            return false;
        }

        $v = array_map('intval', str_split($vkn));
        
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $digit = ($v[$i] + (9 - $i)) % 10;
            $sum += ($digit * (2 ** (9 - $i))) % 9;
            if ($digit !== 0 && $sum % 9 === 0) {
                $sum += 9;
            }
        }
        
        return ($sum % 10) === $v[9];
    }
}

if (!function_exists('tckn_validate')) {
    /**
     * Validate Turkish ID Number (TCKN)
     */
    function tckn_validate(string $tckn): bool
    {
        $tckn = preg_replace('/[^0-9]/', '', $tckn);
        
        if (strlen($tckn) !== 11 || $tckn[0] === '0') {
            return false;
        }

        $digits = array_map('intval', str_split($tckn));
        
        $sum1 = ($digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8]) * 7;
        $sum2 = $digits[1] + $digits[3] + $digits[5] + $digits[7];
        $check10 = ($sum1 - $sum2) % 10;
        
        if ($check10 !== $digits[9]) {
            return false;
        }
        
        $sum11 = array_sum(array_slice($digits, 0, 10));
        return ($sum11 % 10) === $digits[10];
    }
}
