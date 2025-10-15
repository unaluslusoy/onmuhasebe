<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

echo "=== Subscription API Test ===\n\n";

// 1. Login to get token
echo "1. Login...\n";
$loginData = json_encode([
    'email' => 'testuser@example.com',
    'password' => 'Test1234!'
]);

$ch = curl_init('http://localhost:8000/api/auth/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$loginResponse = curl_exec($ch);
$loginResult = json_decode($loginResponse, true);

if (!$loginResult['success']) {
    die("Login failed: " . $loginResult['message'] . "\n");
}

$token = $loginResult['data']['tokens']['access_token'];
echo "✅ Login successful! Token: " . substr($token, 0, 20) . "...\n\n";

// 2. Get current subscription
echo "2. Get current subscription...\n";
$ch = curl_init('http://localhost:8000/api/subscriptions/current');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$subResponse = curl_exec($ch);
$subResult = json_decode($subResponse, true);

if ($subResult['success']) {
    echo "✅ Current subscription:\n";
    echo "   Status: " . $subResult['data']['subscription']['status'] . "\n";
    echo "   Plan: " . $subResult['data']['subscription']['plan_type'] . "\n";
    echo "   Days remaining: " . $subResult['data']['days_remaining'] . "\n";
    echo "   Trial ends: " . $subResult['data']['subscription']['trial_ends_at'] . "\n";
} else {
    echo "❌ Error: " . $subResult['message'] . "\n";
}

echo "\n3. Get available plans...\n";
$ch = curl_init('http://localhost:8000/api/subscriptions/plans');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);

$plansResponse = curl_exec($ch);
$plansResult = json_decode($plansResponse, true);

if ($plansResult['success']) {
    echo "✅ Available plans:\n";
    foreach ($plansResult['data']['plans'] as $plan) {
        echo "   - " . $plan['name'] . " (" . $plan['slug'] . "): ₺" . $plan['price'] . "/month\n";
        echo "     Features: " . ($plan['features'] ?? 'N/A') . "\n";
    }
} else {
    echo "❌ Error: " . $plansResult['message'] . "\n";
}

echo "\n=== Test completed ===\n";

curl_close($ch);
