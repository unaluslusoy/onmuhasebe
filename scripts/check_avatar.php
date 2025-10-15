<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    
    $stmt = $pdo->query('SELECT id, full_name, email, avatar FROM users WHERE id=1');
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "User Avatar Info:\n";
    echo json_encode($user, JSON_PRETTY_PRINT) . "\n\n";
    
    // Avatar dosyasını kontrol et
    if ($user['avatar']) {
        $avatarPath = __DIR__ . '/../storage/uploads/avatars/' . $user['avatar'];
        echo "Avatar Path: $avatarPath\n";
        echo "File Exists: " . (file_exists($avatarPath) ? 'YES' : 'NO') . "\n";
        
        if (file_exists($avatarPath)) {
            echo "File Size: " . filesize($avatarPath) . " bytes\n";
        }
    } else {
        echo "No avatar set in database\n";
    }
    
    // Storage klasörünü kontrol et
    $avatarsDir = __DIR__ . '/../storage/uploads/avatars/';
    echo "\nFiles in avatars directory:\n";
    if (is_dir($avatarsDir)) {
        $files = scandir($avatarsDir);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "  - $file\n";
            }
        }
    } else {
        echo "Directory does not exist!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
