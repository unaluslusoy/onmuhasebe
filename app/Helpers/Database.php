<?php

namespace App\Helpers;

use PDO;
use PDOException;

/**
 * Database Helper
 * Singleton PDO database connection
 */
class Database
{
    private static ?PDO $instance = null;

    /**
     * Get PDO instance (singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    "%s:host=%s;port=%s;dbname=%s;charset=%s",
                    $_ENV['DB_CONNECTION'] ?? 'mysql',
                    $_ENV['DB_HOST'] ?? '127.0.0.1',
                    $_ENV['DB_PORT'] ?? '3306',
                    $_ENV['DB_DATABASE'] ?? 'onmuhasebe',
                    $_ENV['DB_CHARSET'] ?? 'utf8mb4'
                );

                self::$instance = new PDO(
                    $dsn,
                    $_ENV['DB_USERNAME'] ?? 'root',
                    $_ENV['DB_PASSWORD'] ?? '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

            } catch (PDOException $e) {
                Logger::error('Database connection failed', [
                    'error' => $e->getMessage()
                ]);

                throw new \Exception('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    /**
     * Close the connection (mainly for testing)
     */
    public static function disconnect(): void
    {
        self::$instance = null;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
