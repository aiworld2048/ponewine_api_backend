<?php
/**
 * Test Buffalo API on Live Domain
 * Testing: https://gamestar77.online/api/buffalo/get-user-balance
 */

// Configuration
$baseUrl = 'https://gamestar77.online/api/buffalo';
$uid = 'gam976fcef560ffb287836329c82317';
$token = 'e2b99ede5af3dcbb4f1b35d4287f1937239a13138354d2b8162ba6c328d5e847';

echo "=== Testing Buffalo API on Live Domain ===\n\n";
echo "Base URL: {$baseUrl}\n";
echo "UID: {$uid}\n";
echo "Token: {$token}\n\n";

// Test 1: Get User Balance
echo "1. Testing Get User Balance on Live Domain...\n";
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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For testing only

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "URL: {$getBalanceUrl}\n";
echo "Request: {$getBalanceData}\n";
echo "HTTP Code: {$httpCode}\n";
if ($curlError) {
    echo "Curl Error: {$curlError}\n";
}
echo "Response: {$response}\n\n";

// Test 2: Change Balance
echo "2. Testing Change Balance on Live Domain...\n";
$changeBalanceUrl = $baseUrl . '/change-balance';
$changeBalanceData = json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 5.00,
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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // For testing only

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "URL: {$changeBalanceUrl}\n";
echo "Request: {$changeBalanceData}\n";
echo "HTTP Code: {$httpCode}\n";
if ($curlError) {
    echo "Curl Error: {$curlError}\n";
}
echo "Response: {$response}\n\n";

echo "=== Test Complete ===\n";
echo "If you see HTTP 200 with code: 1, the live API is working!\n";
echo "If you see HTTP 404, the routes might not be deployed.\n";
echo "If you see HTTP 500, check the server logs.\n";
