<?php

/**
 * API Test Script (Port 8000)
 * Test authentication endpoints on PHP built-in server
 * 
 * Usage: php scripts/test_api_local.php
 */

echo "===========================================\n";
echo "API Test Script (Local Server)\n";
echo "===========================================\n\n";

$baseUrl = 'http://localhost:8000/api';

// Test 1: Health Check
echo "[1/6] Testing health endpoint...\n";
$response = makeRequest('GET', $baseUrl . '/health');
printResponse($response);

// Test 2: Login with default admin
echo "\n[2/6] Testing login with admin@onmuhasebe.com...\n";
$loginData = [
    'email' => 'admin@onmuhasebe.com',
    'password' => 'Admin123!'
];
$response = makeRequest('POST', $baseUrl . '/auth/login', $loginData);
printResponse($response);

$tokens = null;
if ($response['status'] === 200 && isset($response['body']['data']['tokens'])) {
    $tokens = $response['body']['data']['tokens'];
    echo "✓ Access token: " . substr($tokens['access_token'], 0, 30) . "...\n";
    echo "✓ Refresh token: " . substr($tokens['refresh_token'], 0, 30) . "...\n";
}

// Test 3: Get User Info (Protected)
if ($tokens) {
    echo "\n[3/6] Testing protected route GET /auth/me...\n";
    $response = makeRequest('GET', $baseUrl . '/auth/me', null, [
        'Authorization: Bearer ' . $tokens['access_token']
    ]);
    printResponse($response);
}

// Test 4: Register New User
echo "\n[4/6] Testing user registration...\n";
$timestamp = time();
$registerData = [
    'email' => "test{$timestamp}@example.com",
    'password' => 'Test123!',
    'password_confirmation' => 'Test123!',
    'full_name' => 'Test User',
    'phone' => '05551234567'
];
$response = makeRequest('POST', $baseUrl . '/auth/register', $registerData);
printResponse($response);

// Test 5: Refresh Token
if ($tokens) {
    echo "\n[5/6] Testing token refresh...\n";
    $refreshData = ['refresh_token' => $tokens['refresh_token']];
    $response = makeRequest('POST', $baseUrl . '/auth/refresh', $refreshData);
    printResponse($response);
    
    if ($response['status'] === 200 && isset($response['body']['data'])) {
        echo "✓ New access token received\n";
    }
}

// Test 6: Logout
if ($tokens) {
    echo "\n[6/6] Testing logout...\n";
    $response = makeRequest('POST', $baseUrl . '/auth/logout', null, [
        'Authorization: Bearer ' . $tokens['access_token']
    ]);
    printResponse($response);
}

echo "\n===========================================\n";
echo "✅ All API tests completed!\n";
echo "===========================================\n";

/**
 * Make HTTP request
 */
function makeRequest($method, $url, $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $defaultHeaders = ['Content-Type: application/json'];
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($defaultHeaders, $headers));
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'status' => $statusCode,
        'body' => json_decode($response, true),
        'error' => $error,
        'raw' => $response
    ];
}

/**
 * Print response
 */
function printResponse($response) {
    if ($response['error']) {
        echo "✗ cURL Error: {$response['error']}\n";
        echo "Raw response: {$response['raw']}\n";
        return;
    }
    
    $status = $response['status'];
    $symbol = $status >= 200 && $status < 300 ? '✓' : '✗';
    
    echo "{$symbol} HTTP {$status}\n";
    
    if ($response['body']) {
        $json = json_encode($response['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        echo "Response:\n{$json}\n";
    } else {
        echo "Raw: {$response['raw']}\n";
    }
}
