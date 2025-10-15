<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "=== Users Table Structure ===\n\n";

$stmt = $db->query('DESCRIBE users');
while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}

echo "\n=== Add company_id column ===\n";

try {
    $db->exec("ALTER TABLE users ADD COLUMN company_id INT(10) UNSIGNED NULL AFTER role");
    echo "✅ Column added successfully!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "⚠️  Column already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

// Add foreign key
echo "\n=== Add foreign key ===\n";
try {
    $db->exec("ALTER TABLE users ADD CONSTRAINT fk_user_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL");
    echo "✅ Foreign key added successfully!\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
        echo "⚠️  Foreign key already exists\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Done ===\n";
