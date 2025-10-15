<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "=== Subscription Plans ===\n\n";

$stmt = $db->query('SELECT * FROM subscription_plans');
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($plans)) {
    echo "❌ No plans found!\n\nAdding default plans...\n\n";
    
    $defaultPlans = [
        ['trial', 'Deneme', 0, '{"feature1": true}', 1, 50, 5, 1],
        ['basic', 'Temel', 99, '{"feature1": true, "feature2": true}', 3, 200, 50, 1],
        ['professional', 'Profesyonel', 199, '{"feature1": true, "feature2": true, "feature3": true}', 10, 1000, 500, 1],
        ['enterprise', 'Kurumsal', 499, '{"feature1": true, "feature2": true, "feature3": true, "feature4": true}', 999, 99999, 99999, 1]
    ];
    
    $insertSql = "INSERT INTO subscription_plans (slug, name, price, features, max_users, max_invoices_per_month, max_storage_gb, is_active) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($insertSql);
    
    foreach ($defaultPlans as $plan) {
        $stmt->execute($plan);
        echo "✅ Added: " . $plan[1] . " (₺" . $plan[2] . ")\n";
    }
    
    echo "\n";
    $stmt = $db->query('SELECT * FROM subscription_plans');
    $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

echo "Current plans:\n";
foreach ($plans as $plan) {
    echo "  - " . $plan['name'] . " (" . $plan['slug'] . "): ₺" . $plan['price'] . "/mo\n";
    echo "    Max users: " . $plan['max_users'] . ", Max invoices: " . $plan['max_invoices_per_month'] . "\n";
}
