<?php
/**
 * Test Buffalo API with Laravel Bootstrap
 * This script properly generates UID and token using BuffaloGameService
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\BuffaloGameService;

echo "=== Testing Buffalo API with Laravel Bootstrap ===\n\n";

// Find or create test user
$user = User::where('user_name', 'player001')->first();

if (!$user) {
    echo "Creating test user...\n";
    $user = User::create([
        'user_name' => 'player001',
        'email' => 'player001@test.com',
        'password' => bcrypt('password'),
        'balance' => 1000.00,
        'type' => \App\Enums\UserType::Player->value
    ]);
    echo "Test user created successfully!\n";
} else {
    echo "Test user found: {$user->user_name}\n";
}

// Generate proper UID and token using BuffaloGameService
$auth = BuffaloGameService::generateBuffaloAuth($user);
$uid = $auth['uid'];
$token = $auth['token'];

echo "\nGenerated UID: {$uid} (Length: " . strlen($uid) . ")\n";
echo "Generated Token: {$token} (Length: " . strlen($token) . ")\n\n";

// Test token verification
echo "Testing token verification...\n";
$isValid = BuffaloGameService::verifyToken($uid, $token);
echo "Token verification: " . ($isValid ? "VALID" : "INVALID") . "\n\n";

// Configuration for API testing
$baseUrl = 'http://localhost:8000/api/buffalo';

echo "=== API Test Examples ===\n\n";

// Test 1: Get User Balance
echo "1. GET USER BALANCE:\n";
echo "POST {$baseUrl}/get-user-balance\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token
], JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Change Balance
echo "2. CHANGE BALANCE:\n";
echo "POST {$baseUrl}/change-balance\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 10.00,
    'bet' => 1.00,
    'win' => 1.00,
    'gameId' => 1234
], JSON_PRETTY_PRINT) . "\n\n";

echo "=== Copy these values for Postman testing ===\n";
echo "UID: {$uid}\n";
echo "Token: {$token}\n";
echo "\nExpected Response Format:\n";
echo "Get Balance Response: {\"code\": 1, \"msg\": \"Success\", \"balance\": 1000.00}\n";
echo "Change Balance Response: {\"code\": 1, \"msg\": \"Success\"}\n";

// Test the API endpoints
echo "\n=== Testing API Endpoints ===\n";

// Test Get User Balance
echo "Testing Get User Balance...\n";
$getBalanceUrl = $baseUrl . '/get-user-balance';
$getBalanceData = json_encode([
    'uid' => $uid,
    'token' => $token
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $getBalanceUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $getBalanceData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

// Test Change Balance
echo "Testing Change Balance...\n";
$changeBalanceUrl = $baseUrl . '/change-balance';
$changeBalanceData = json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 10.00,
    'bet' => 1.00,
    'win' => 1.00,
    'gameId' => 1234
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $changeBalanceUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $changeBalanceData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

echo "=== Test Complete ===\n";
