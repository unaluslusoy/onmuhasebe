<?php
// Quick DB Test
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "DB Config:\n";
echo "Host: " . env('DB_HOST') . "\n";
echo "Database: " . env('DB_DATABASE') . "\n";
echo "Username: " . env('DB_USERNAME') . "\n\n";

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        env('DB_HOST'),
        env('DB_PORT', 3306),
        env('DB_DATABASE'),
        env('DB_CHARSET', 'utf8mb4')
    );
    
    $pdo = new PDO($dsn, env('DB_USERNAME'), env('DB_PASSWORD'));
    echo "✓ Connection successful!\n\n";
    
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Tables (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    if (in_array('users', $tables)) {
        $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "\n✓ users table exists with {$count['count']} row(s)\n";
    } else {
        echo "\n✗ users table NOT FOUND!\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
