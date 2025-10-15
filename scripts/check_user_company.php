<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "=== Check User Company ID ===\n\n";

$stmt = $db->query('SELECT id, email, company_id FROM users WHERE email = "testuser@example.com"');
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "User found:\n";
    echo "  ID: " . $user['id'] . "\n";
    echo "  Email: " . $user['email'] . "\n";
    echo "  Company ID: " . ($user['company_id'] ?? 'NULL') . "\n";
    
    if ($user['company_id']) {
        echo "\n✅ User has company_id\n";
    } else {
        echo "\n❌ User has NO company_id!\n";
        echo "\nFixing...\n";
        
        // Find company owned by this user
        $companyStmt = $db->prepare('SELECT id FROM companies WHERE owner_id = ?');
        $companyStmt->execute([$user['id']]);
        $company = $companyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($company) {
            $updateStmt = $db->prepare('UPDATE users SET company_id = ? WHERE id = ?');
            $updateStmt->execute([$company['id'], $user['id']]);
            echo "✅ Fixed! Company ID " . $company['id'] . " linked to user.\n";
        } else {
            echo "❌ No company found for this user!\n";
        }
    }
} else {
    echo "❌ User not found\n";
}
