<?php
/**
 * Test Invalid UID to Check User Lookup Logic
 */

echo "=== Testing Invalid UID to Check User Lookup ===\n\n";

$baseUrl = 'https://gamestar77.online/api/buffalo/get-user-balance';

// Test with completely invalid UID format
$testData = json_encode([
    'uid' => 'invalid_uid_format_12345',
    'token' => 'test123456789012345678901234567890123456789012345678901234567890'
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

echo "Invalid UID Test:\n";
echo "UID: invalid_uid_format_12345\n";
echo "Response: {$response}\n\n";

// Test with missing uid field
$testData2 = json_encode([
    'token' => 'test123456789012345678901234567890123456789012345678901234567890'
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData2);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response2 = curl_exec($ch);
$httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Missing UID Test:\n";
echo "Response: {$response2}\n\n";

echo "=== Analysis ===\n";
if (strpos($response, 'User not found') !== false) {
    echo "✓ Server can detect 'User not found' - UID lookup is working\n";
} elseif (strpos($response, 'Invalid token') !== false) {
    echo "⚠ Server returns 'Invalid token' even for invalid UID - token verification happens first\n";
}

if (strpos($response2, 'required') !== false || strpos($response2, 'validation') !== false) {
    echo "✓ Server validates required fields\n";
}
