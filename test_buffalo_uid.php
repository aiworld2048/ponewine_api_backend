<?php

/**
 * Buffalo Game UID Generation Test Script
 * 
 * This script demonstrates how the UID prefix system works
 * as requested by the API provider.
 */

require_once 'vendor/autoload.php';

use App\Services\BuffaloGameService;

echo "=== Buffalo Game UID Generation Test ===\n\n";

// Test data - using actual UUID format like API provider
$testUsers = [
    '596e0c89-65d3-434b-911c-dd67c58b6304',
    'player001',
    'user123',
    'gamer456',
    'testuser'
];

echo "Site Configuration:\n";
$siteInfo = BuffaloGameService::getSiteInfo();
echo "- Site Name: {$siteInfo['site_name']}\n";
echo "- Site Prefix: {$siteInfo['site_prefix']}\n\n";

echo "UID Generation Examples (32-char UUID + 64-char Token):\n";
echo str_repeat("-", 60) . "\n";

foreach ($testUsers as $userName) {
    $uid = BuffaloGameService::generateUid($userName);
    $token = BuffaloGameService::generateToken($uid);
    $extractedUserName = BuffaloGameService::extractUserNameFromUid($uid);
    
    echo "Original Username: {$userName}\n";
    echo "Generated UID (32 chars): {$uid} [" . strlen($uid) . "]\n";
    echo "Generated Token (64 chars): {$token} [" . strlen($token) . "]\n";
    echo "Extracted Username: {$extractedUserName}\n";
    echo "Verification: " . (BuffaloGameService::verifyToken($uid, $token) ? 'PASS' : 'FAIL') . "\n";
    echo str_repeat("-", 60) . "\n";
}

echo "\nRoom Configuration:\n";
echo str_repeat("-", 50) . "\n";
$rooms = BuffaloGameService::getRoomConfig();
foreach ($rooms as $roomId => $config) {
    echo "Room {$roomId}: {$config['name']} - Min Bet: {$config['min_bet']} cents ({$config['level']})\n";
}

echo "\nAPI Provider Format Examples (No Secret Key):\n";
echo str_repeat("-", 60) . "\n";

// Example 1: API Provider format (UUID-based)
$apiProviderUid = '596e0c89-65d3-434b-911c-dd67c58b6304';
$apiProviderToken = 'e576f40f50271d330ca9650b474801ab672015acd920fe84fb67b6988c46353a';

echo "API Provider Example (32-char UUID + 64-char Token):\n";
echo "UID (32 chars): {$apiProviderUid} [" . strlen($apiProviderUid) . "]\n";
echo "Token (64 chars): {$apiProviderToken} [" . strlen($apiProviderToken) . "]\n";

// Example 2: Our site format (32-char UUID + 64-char Token)
echo "\nOur Site Format Example (32-char UUID + 64-char Token):\n";
$testUser = 'player001';
$uid = BuffaloGameService::generateUid($testUser);
$token = BuffaloGameService::generateToken($uid);

echo "For user: {$testUser}\n";
echo "UID (32 chars): {$uid} [" . strlen($uid) . "]\n";
echo "Token (64 chars): {$token} [" . strlen($token) . "]\n";

// Example game URL
$baseUrl = 'http://bk1.buffalo125.com/';
$gameUrl = $baseUrl . '?' . http_build_query([
    'gameId' => '23',
    'roomId' => '1',
    'uid' => $uid,
    'token' => $token,
    'lobbyUrl' => 'https://gamestar77.online'
]);

echo "Game URL: {$gameUrl}\n";

echo "\nPostman Test Examples:\n";
echo str_repeat("-", 50) . "\n";

// Get User Balance example
echo "GET USER BALANCE:\n";
echo "POST /api/buffalo/get-user-balance\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token
], JSON_PRETTY_PRINT) . "\n\n";

// Change Balance example
echo "CHANGE BALANCE:\n";
echo "POST /api/buffalo/change-balance\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token,
    'changemoney' => 10.00, // Provider confirmed: no cents conversion needed
    'bet' => 1.00, // Provider confirmed: no cents conversion needed
    'win' => 1.00, // Provider confirmed: should be integer, no cents conversion needed
    'gameId' => 1234 // Provider confirmed: should be integer
], JSON_PRETTY_PRINT) . "\n\n";

echo "\n=== Test Complete ===\n";
