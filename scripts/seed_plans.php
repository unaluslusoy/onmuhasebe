<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "=== Seed Subscription Plans ===\n\n";

// Clear existing plans
$db->exec('DELETE FROM subscription_plans');
echo "✅ Cleared existing plans\n\n";

$plans = [
    ['trial', 'Deneme', 'Ücretsiz 30 günlük deneme süresi', 0, 0, '{"invoices":"50","support":"Email"}', 1, 50, 5, 1, 1],
    ['basic', 'Temel', 'Küçük işletmeler için ideal', 99, 990, '{"invoices":"200","support":"Email","reports":"Temel"}', 3, 200, 50, 1, 2],
    ['professional', 'Profesyonel', 'Büyüyen işletmeler için', 199, 1990, '{"invoices":"1000","support":"Öncelikli","reports":"Gelişmiş","api":true}', 10, 1000, 500, 1, 3],
    ['enterprise', 'Kurumsal', 'Büyük şirketler için', 499, 4990, '{"invoices":"Sınırsız","support":"7/24","reports":"Özel","api":true,"custom":true}', 999, 99999, 99999, 1, 4]
];

$stmt = $db->prepare('INSERT INTO subscription_plans (slug, name, description, price_monthly, price_yearly, features, max_users, max_invoices_per_month, max_storage_gb, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

foreach ($plans as $plan) {
    $stmt->execute($plan);
    echo "✅ " . $plan[1] . " - ₺" . $plan[3] . "/ay eklendi\n";
}

echo "\n=== Plans seeded successfully ===\n";
