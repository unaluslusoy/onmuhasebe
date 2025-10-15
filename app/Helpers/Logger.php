<?php

namespace App\Helpers;

/**
 * Logger Class
 * Centralized logging system with multiple log levels and automatic cleanup
 *
 * Usage:
 *   Logger::error('Database connection failed', ['error' => $e->getMessage()]);
 *   Logger::security('Failed login attempt', ['email' => $email, 'ip' => $ip]);
 *   Logger::info('User registered', ['user_id' => $userId]);
 *   Logger::api('Invoice created', ['invoice_id' => $id, 'method' => 'POST']);
 */
class Logger
{
    private static ?string $logPath = null;
    private static int $retentionDays = 30;

    /**
     * Initialize logger
     */
    private static function init(): void
    {
        if (self::$logPath === null) {
            self::$logPath = __DIR__ . '/../../storage/logs/';

            // Create logs directory if it doesn't exist
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }

            // Set retention days from env
            self::$retentionDays = (int)($_ENV['LOG_RETENTION_DAYS'] ?? 30);
        }
    }

    /**
     * Log an error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }

    /**
     * Log a warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }

    /**
     * Log an info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }

    /**
     * Log a debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        // Only log debug in development
        if (($_ENV['APP_ENV'] ?? 'production') === 'local' &&
            ($_ENV['LOG_LEVEL'] ?? 'error') === 'debug') {
            self::log('debug', $message, $context);
        }
    }

    /**
     * Log a security event
     */
    public static function security(string $message, array $context = []): void
    {
        self::log('security', $message, $context);
    }

    /**
     * Log an API request/response
     */
    public static function api(string $message, array $context = []): void
    {
        self::log('api', $message, $context);
    }

    /**
     * Log database queries (slow queries)
     */
    public static function database(string $message, array $context = []): void
    {
        self::log('database', $message, $context);
    }

    /**
     * Log e-Fatura operations
     */
    public static function efatura(string $message, array $context = []): void
    {
        self::log('efatura', $message, $context);
    }

    /**
     * Main logging method
     */
    private static function log(string $level, string $message, array $context = []): void
    {
        self::init();

        // Determine log file
        $filename = self::$logPath . $level . '_' . date('Y-m-d') . '.log';

        // Prepare context
        $contextStr = '';
        if (!empty($context)) {
            // Sanitize sensitive data
            $context = self::sanitizeContext($context);
            $contextStr = ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        // Add request info for security and API logs
        $requestInfo = '';
        if (in_array($level, ['security', 'api'])) {
            $requestInfo = self::getRequestInfo();
        }

        // Format log entry
        $logEntry = sprintf(
            "[%s] [%s] %s%s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $requestInfo,
            $contextStr
        );

        // Write to file
        @file_put_contents($filename, $logEntry, FILE_APPEND | LOCK_EX);

        // Periodic cleanup (1% chance)
        if (rand(1, 100) === 1) {
            self::cleanup();
        }
    }

    /**
     * Get request information
     */
    private static function getRequestInfo(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
        $uri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        return sprintf(
            ' | IP: %s | %s %s | UA: %s',
            $ip,
            $method,
            $uri,
            substr($userAgent, 0, 100)
        );
    }

    /**
     * Sanitize sensitive data from context
     */
    private static function sanitizeContext(array $context): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'secret',
            'jwt',
            'credit_card',
            'card_number',
            'cvv',
            'pin'
        ];

        foreach ($context as $key => $value) {
            // Check if key is sensitive
            foreach ($sensitiveKeys as $sensitiveKey) {
                if (stripos($key, $sensitiveKey) !== false) {
                    $context[$key] = '***REDACTED***';
                    break;
                }
            }

            // Recursively sanitize arrays
            if (is_array($value)) {
                $context[$key] = self::sanitizeContext($value);
            }
        }

        return $context;
    }

    /**
     * Clean up old log files
     */
    private static function cleanup(): void
    {
        self::init();

        $cutoffTime = time() - (self::$retentionDays * 24 * 60 * 60);
        $files = glob(self::$logPath . '*.log');

        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                @unlink($file);
            }
        }
    }

    /**
     * Get log files for a specific level
     */
    public static function getLogFiles(string $level = null, int $limit = 10): array
    {
        self::init();

        $pattern = $level
            ? self::$logPath . $level . '_*.log'
            : self::$logPath . '*.log';

        $files = glob($pattern);

        // Sort by date (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        return array_slice($files, 0, $limit);
    }

    /**
     * Read log file with pagination
     */
    public static function readLog(string $filepath, int $offset = 0, int $limit = 100): array
    {
        if (!file_exists($filepath)) {
            return [];
        }

        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            return [];
        }

        // Reverse to show newest first
        $lines = array_reverse($lines);

        // Paginate
        $total = count($lines);
        $lines = array_slice($lines, $offset, $limit);

        return [
            'lines' => $lines,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
            'has_more' => ($offset + $limit) < $total
        ];
    }

    /**
     * Search logs
     */
    public static function search(string $query, string $level = null, string $date = null): array
    {
        self::init();

        $pattern = $level && $date
            ? self::$logPath . $level . '_' . $date . '.log'
            : ($level
                ? self::$logPath . $level . '_*.log'
                : self::$logPath . '*.log');

        $files = glob($pattern);
        $results = [];

        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($lines === false) {
                continue;
            }

            foreach ($lines as $lineNum => $line) {
                if (stripos($line, $query) !== false) {
                    $results[] = [
                        'file' => basename($file),
                        'line' => $lineNum + 1,
                        'content' => $line
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Get log statistics
     */
    public static function getStatistics(string $date = null): array
    {
        self::init();

        $date = $date ?? date('Y-m-d');
        $stats = [
            'date' => $date,
            'levels' => []
        ];

        $levels = ['error', 'warning', 'info', 'debug', 'security', 'api', 'database', 'efatura'];

        foreach ($levels as $level) {
            $file = self::$logPath . $level . '_' . $date . '.log';

            if (file_exists($file)) {
                $lines = count(file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                $size = filesize($file);

                $stats['levels'][$level] = [
                    'count' => $lines,
                    'size' => $size,
                    'size_human' => self::formatBytes($size)
                ];
            } else {
                $stats['levels'][$level] = [
                    'count' => 0,
                    'size' => 0,
                    'size_human' => '0 B'
                ];
            }
        }

        return $stats;
    }

    /**
     * Format bytes to human readable
     */
    private static function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
