<?php

/**
 * Check Server IP Address
 * 
 * This script helps identify your server's IP address for provider whitelisting.
 * 
 * Usage: php check_server_ip.php
 */

echo "\n";
echo "========================================\n";
echo "Server IP Address Checker\n";
echo "========================================\n\n";

// Method 1: SERVER_ADDR (server's IP)
$serverAddr = $_SERVER['SERVER_ADDR'] ?? 'Not available';
echo "1. SERVER_ADDR (Server's IP):\n";
echo "   {$serverAddr}\n\n";

// Method 2: REMOTE_ADDR (client's IP)
$remoteAddr = $_SERVER['REMOTE_ADDR'] ?? 'Not available';
echo "2. REMOTE_ADDR (Client's IP):\n";
echo "   {$remoteAddr}\n\n";

// Method 3: HTTP_X_FORWARDED_FOR (if behind proxy)
$forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 'Not set';
echo "3. HTTP_X_FORWARDED_FOR (Proxy IP):\n";
echo "   {$forwardedFor}\n\n";

// Method 4: HTTP_X_REAL_IP (if behind proxy)
$realIp = $_SERVER['HTTP_X_REAL_IP'] ?? 'Not set';
echo "4. HTTP_X_REAL_IP (Real IP):\n";
echo "   {$realIp}\n\n";

// Method 5: Try to get public IP via external service
echo "5. Public IP (via external service):\n";
try {
    $publicIp = @file_get_contents('https://api.ipify.org');
    if ($publicIp) {
        echo "   {$publicIp}\n\n";
    } else {
        echo "   Could not determine (service unavailable)\n\n";
    }
} catch (Exception $e) {
    echo "   Could not determine: {$e->getMessage()}\n\n";
}

// Summary
echo "========================================\n";
echo "Summary for Provider Whitelisting:\n";
echo "========================================\n\n";

if ($serverAddr !== 'Not available') {
    echo "✅ PRIMARY IP to whitelist: {$serverAddr}\n";
    echo "   (This is likely what the provider sees)\n\n";
}

if ($publicIp && $publicIp !== $serverAddr) {
    echo "⚠️  Note: Public IP differs from SERVER_ADDR\n";
    echo "   Public IP: {$publicIp}\n";
    echo "   You may need to whitelist BOTH IPs\n\n";
}

echo "========================================\n";
echo "Next Steps:\n";
echo "========================================\n";
echo "1. Contact Buffalo game provider\n";
echo "2. Provide them with your server IP: {$serverAddr}\n";
echo "3. Request: 'Please whitelist this IP for Game Login API'\n";
echo "4. API Endpoint: https://api-ms3.african-buffalo.club/api/game-login\n";
echo "5. Wait for confirmation\n";
echo "6. Test again\n\n";

