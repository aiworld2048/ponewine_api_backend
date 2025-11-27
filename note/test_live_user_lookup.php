<?php
/**
 * Test User Lookup on Live Domain
 * This will help us understand what users exist and generate correct tokens
 */

echo "=== Testing Live Domain User Lookup ===\n\n";

// Test different UID formats to see what works
$testUids = [
    'gam976fcef560ffb287836329c82317', // Our generated UID
    'gamplayer001', // Simple format
    'player001', // Just username
    'gam-player001', // With dash
];

$baseUrl = 'https://gamestar77.online/api/buffalo/get-user-balance';

foreach ($testUids as $uid) {
    echo "Testing UID: {$uid}\n";
    
    // Try with a simple token first
    $testData = json_encode([
        'uid' => $uid,
        'token' => 'test123456789012345678901234567890123456789012345678901234567890' // 64 chars
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
    
    echo "  Response: {$response}\n";
    
    if (strpos($response, 'User not found') !== false) {
        echo "  ✓ User not found - UID format might be correct but user doesn't exist\n";
    } elseif (strpos($response, 'Invalid token') !== false) {
        echo "  ✓ Invalid token - UID format might be correct but token is wrong\n";
    } elseif (strpos($response, '"code":1') !== false) {
        echo "  🎉 SUCCESS! This UID/token combination works!\n";
        break;
    }
    echo "\n";
}

echo "=== Test Complete ===\n";
echo "If we see 'User not found' for some UIDs, those users don't exist on live server.\n";
echo "If we see 'Invalid token', the UID format might be correct but token is wrong.\n";
