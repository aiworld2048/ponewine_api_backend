<?php
/**
 * Test Buffalo API Endpoints
 * This script tests the Buffalo API endpoints using curl
 */

// Configuration
$baseUrl = 'http://localhost:8000/api/buffalo';
$uid = 'gama0308ba8f455a9b04d7dd794a1f2';
$token = '05446521831ee6cd322d586bfbcf5247374649ffc1de840ed55a3e4ddf14e516';

echo "=== Testing Buffalo API Endpoints ===\n\n";

// Test 1: Get User Balance
echo "1. Testing Get User Balance...\n";
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

echo "URL: {$getBalanceUrl}\n";
echo "Request: {$getBalanceData}\n";
echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

// Test 2: Change Balance
echo "2. Testing Change Balance...\n";
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

echo "URL: {$changeBalanceUrl}\n";
echo "Request: {$changeBalanceData}\n";
echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

echo "=== Test Complete ===\n";
echo "If you see HTTP 200 responses, the API is working correctly!\n";
echo "If you see HTTP 500 or other errors, check the Laravel logs.\n";
