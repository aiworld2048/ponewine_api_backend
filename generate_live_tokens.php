<?php
/**
 * Generate Tokens for Live Server Testing
 * Try different token generation approaches
 */

echo "=== Generating Tokens for Live Server Testing ===\n\n";

// Test different token generation methods
$username = 'player001';
$siteName = 'gamestar77.online';
$userId = 1; // Assuming user ID 1

echo "Testing different token generation methods:\n\n";

// Method 1: Simple hash (like our local)
$token1 = hash('sha256', $username . $siteName . $userId);
echo "Method 1 (username + site + userid): {$token1}\n";

// Method 2: Just username + site
$token2 = hash('sha256', $username . $siteName);
echo "Method 2 (username + site): {$token2}\n";

// Method 3: Just username
$token3 = hash('sha256', $username);
echo "Method 3 (username only): {$token3}\n";

// Method 4: Timestamp based
$token4 = hash('sha256', $username . time());
echo "Method 4 (username + timestamp): {$token4}\n";

// Method 5: MD5 instead of SHA256
$token5 = md5($username . $siteName . $userId);
echo "Method 5 (MD5): {$token5}\n";

// Method 6: Double hash
$hash1 = hash('sha256', $username . $siteName . $userId);
$token6 = hash('sha256', $hash1);
echo "Method 6 (double hash): {$token6}\n";

echo "\nNow testing each token with the live server:\n\n";

$baseUrl = 'https://gamestar77.online/api/buffalo/get-user-balance';
$uid = 'gam976fcef560ffb287836329c82317'; // Our generated UID

$tokens = [
    'Method 1' => $token1,
    'Method 2' => $token2,
    'Method 3' => $token3,
    'Method 4' => $token4,
    'Method 5' => $token5,
    'Method 6' => $token6
];

foreach ($tokens as $method => $token) {
    echo "Testing {$method}:\n";
    echo "Token: {$token}\n";
    
    $testData = json_encode([
        'uid' => $uid,
        'token' => $token
    ]);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Response: {$response}\n";
    
    if (strpos($response, '"code":1') !== false) {
        echo "🎉 SUCCESS! {$method} token works!\n";
        echo "Use this UID: {$uid}\n";
        echo "Use this Token: {$token}\n";
        break;
    }
    echo "\n";
}

echo "=== Test Complete ===\n";
