<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "=== Companies Table Structure ===\n\n";

$stmt = $db->query('DESCRIBE companies');
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo $col['Field'] . ' - ' . $col['Type'] . ' - ' . $col['Null'] . ' - ' . $col['Default'] . "\n";
}

echo "\n=== Test Insert ===\n";

try {
    $sql = "INSERT INTO companies (name, email, country) VALUES ('Test Company', 'test@test.com', 'Turkey')";
    $db->exec($sql);
    echo "✅ Insert successful! ID: " . $db->lastInsertId() . "\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
