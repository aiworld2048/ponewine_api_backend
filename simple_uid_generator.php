<?php
/**
 * Simple UID and Token Generator for Buffalo Game Testing
 * This script generates UID and token without requiring Laravel bootstrap
 */

// Site configuration
$SITE_NAME = 'gamestar77.online';
$SITE_PREFIX = 'gam'; // First 3 characters of site name

/**
 * Generate 32-character UUID based on username and site prefix
 */
function generateUid($userName) {
    global $SITE_PREFIX;
    
    // Create hash from username
    $hash = md5($userName);
    
    // Take first 28 characters from hash
    $hashPart = substr($hash, 0, 28);
    
    // Combine site prefix (3 chars) + hash (28 chars) + padding (1 char) = 32 chars
    $uid = $SITE_PREFIX . $hashPart;
    
    // Ensure exactly 32 characters
    return substr($uid, 0, 32);
}

/**
 * Generate 64-character token
 */
function generateToken($uid) {
    global $SITE_NAME;
    
    // Create token using UID + site name + timestamp
    $data = $uid . $SITE_NAME . time();
    
    // Generate SHA256 hash and repeat to get 64 characters
    $hash = hash('sha256', $data);
    $token = $hash . hash('sha256', $hash);
    
    // Ensure exactly 64 characters
    return substr($token, 0, 64);
}

// Test with sample username
$testUserName = 'player001';
$uid = generateUid($testUserName);
$token = generateToken($uid);

echo "=== Buffalo Game UID & Token Generator ===\n\n";
echo "Site Configuration:\n";
echo "- Site Name: {$SITE_NAME}\n";
echo "- Site Prefix: {$SITE_PREFIX}\n\n";

echo "Test Username: {$testUserName}\n";
echo "Generated UID: {$uid} (Length: " . strlen($uid) . ")\n";
echo "Generated Token: {$token} (Length: " . strlen($token) . ")\n\n";

echo "=== Postman Test Examples ===\n\n";

// Get User Balance example
echo "1. GET USER BALANCE:\n";
echo "POST /api/buffalo/get-user-balance\n";
echo "Content-Type: application/json\n\n";
echo json_encode([
    'uid' => $uid,
    'token' => $token
], JSON_PRETTY_PRINT) . "\n\n";

// Change Balance example
echo "2. CHANGE BALANCE:\n";
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

echo "=== Game URL Generation ===\n";
$lobbyUrl = 'https://gamestar77.online';
$gameUrl = "https://buffalo-game-provider.com/game?uid={$uid}&token={$token}&lobby={$lobbyUrl}";
echo "Game URL: {$gameUrl}\n\n";

echo "=== Copy these values for Postman testing ===\n";
echo "UID: {$uid}\n";
echo "Token: {$token}\n";
echo "\nExpected Response Format:\n";
echo "Get Balance Response: {\"code\": 1, \"msg\": \"Success\", \"balance\": 1000.00}\n";
echo "Change Balance Response: {\"code\": 1, \"msg\": \"Success\"}\n";
