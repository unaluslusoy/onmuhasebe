<?php

/**
 * Database Migration Runner
 * Executes all SQL migration files in order
 * 
 * Usage: php scripts/migrate.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$host = env('DB_HOST', '127.0.0.1');
$port = env('DB_PORT', 3306);
$database = env('DB_DATABASE', 'onmuhasebe');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');
$charset = env('DB_CHARSET', 'utf8mb4');
$collation = env('DB_COLLATION', 'utf8mb4_unicode_ci');

echo "===========================================\n";
echo "Database Migration Runner\n";
echo "===========================================\n\n";

try {
    // Connect to MySQL without selecting database
    $pdo = new PDO(
        "mysql:host={$host};port={$port}",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true
        ]
    );

    // Create database if not exists
    echo "[1/3] Creating database '{$database}'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` 
                CHARACTER SET {$charset} 
                COLLATE {$collation}");
    echo "✓ Database ready\n\n";

    // Select database
    $pdo->exec("USE `{$database}`");

    // Get all migration files
    $migrationsPath = __DIR__ . '/../database/migrations';
    $files = glob($migrationsPath . '/*.sql');
    sort($files);

    if (empty($files)) {
        echo "No migration files found in {$migrationsPath}\n";
        exit(0);
    }

    echo "[2/3] Running " . count($files) . " migration file(s)...\n";

    // Execute each migration
    foreach ($files as $index => $file) {
        $filename = basename($file);
        echo "  → [{$filename}] ";

        $sql = file_get_contents($file);
        
        // Split by semicolon and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt) && !str_starts_with($stmt, '--')
        );

        foreach ($statements as $statement) {
            $pdo->exec($statement);
        }

        echo "✓\n";
    }

    echo "\n[3/3] Migration completed successfully!\n";
    echo "===========================================\n";

} catch (PDOException $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "===========================================\n";
    exit(1);
}
