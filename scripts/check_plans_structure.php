<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "=== subscription_plans table structure ===\n\n";

$stmt = $db->query('DESCRIBE subscription_plans');
while ($col = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $col['Field'] . " - " . $col['Type'] . "\n";
}
