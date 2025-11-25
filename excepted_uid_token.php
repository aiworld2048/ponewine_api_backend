<?php

/**
 * Buffalo Game - Expected UID and Token Generator
 * 
 * This script helps debug UID and token generation for Buffalo game integration.
 * 
 * Usage:
 *   php excepted_uid_token.php <username>
 *   OR
 *   php excepted_uid_token.php
 *     (will prompt for username)
 */

require __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\BuffaloGameService;
use App\Models\User;
use Illuminate\Support\Facades\Config;

// Get username from command line argument or prompt
$username = $argv[1] ?? null;

if (!$username) {
    echo "Enter username: ";
    $username = trim(fgets(STDIN));
}

if (empty($username)) {
    echo "Error: Username is required!\n";
    echo "Usage: php excepted_uid_token.php <username>\n";
    exit(1);
}

echo "\n";
echo "========================================\n";
echo "Buffalo Game - UID & Token Generator\n";
echo "========================================\n\n";

// Find user in database
$user = User::where('user_name', $username)->first();

if (!$user) {
    echo "⚠️  Warning: User '{$username}' not found in database!\n";
    echo "   Generating UID/Token anyway based on username...\n\n";
} else {
    echo "✅ User found in database:\n";
    echo "   User ID: {$user->id}\n";
    echo "   Username: {$user->user_name}\n";
    echo "   Current Balance: " . number_format($user->balanceFloat, 2) . "\n\n";
}

// Generate UID and Token
echo "Generating UID and Token...\n";
echo "----------------------------------------\n";

$uid = BuffaloGameService::generateUid($username);
$token = BuffaloGameService::generatePersistentToken($username);

// Get configuration
$siteUrl = Config::get('buffalo.site.url', 'https://maxwinmyanmar.pro');
$sitePrefix = Config::get('buffalo.site.prefix', 'mxm');

echo "Configuration:\n";
echo "   Site URL: {$siteUrl}\n";
echo "   Site Prefix: {$sitePrefix}\n\n";

echo "Generated Values:\n";
echo "----------------------------------------\n";
echo "Username: {$username}\n";
echo "UID Length: " . strlen($uid) . " characters\n";
echo "Token Length: " . strlen($token) . " characters\n\n";

echo "UID (Full):\n";
echo "   {$uid}\n\n";

echo "Token (Full):\n";
echo "   {$token}\n\n";

echo "Token (Preview - First 20 + Last 10):\n";
echo "   " . substr($token, 0, 20) . "..." . substr($token, -10) . "\n\n";

// Verify token generation
echo "Verification Test:\n";
echo "----------------------------------------\n";
$isValid = BuffaloGameService::verifyToken($uid, $token);
echo "Token Verification: " . ($isValid ? "✅ VALID" : "❌ INVALID") . "\n\n";

// Extract username from UID (reverse lookup)
echo "Reverse Lookup (Extract username from UID):\n";
echo "----------------------------------------\n";
$extractedUsername = BuffaloGameService::extractUserNameFromUid($uid);
if ($extractedUsername) {
    echo "✅ Successfully extracted: {$extractedUsername}\n";
    echo "   Match: " . ($extractedUsername === $username ? "✅ YES" : "❌ NO") . "\n";
} else {
    echo "❌ Failed to extract username from UID\n";
}
echo "\n";

// Show API request format
echo "API Request Format (Game Login API):\n";
echo "----------------------------------------\n";
$apiPayload = [
    'uid' => $uid,
    'token' => $token,
    'gameId' => 23,
    'roomId' => '1',
    'lobbyUrl' => Config::get('buffalo.game_server_url', 'https://prime.next-api.net'),
    'domain' => Config::get('buffalo.domain', 'prime.com'),
];

echo json_encode($apiPayload, JSON_PRETTY_PRINT) . "\n\n";

// Show webhook request format
echo "Webhook Request Format (get-user-balance):\n";
echo "----------------------------------------\n";
$webhookPayload = [
    'uid' => $uid,
    'token' => $token,
];

echo json_encode($webhookPayload, JSON_PRETTY_PRINT) . "\n\n";

echo "Webhook Request Format (change-balance):\n";
echo "----------------------------------------\n";
$changeBalancePayload = [
    'uid' => $uid,
    'bet_uid' => 'unique-bet-' . time(),
    'token' => $token,
    'changemoney' => -100,
    'bet' => 100,
    'win' => 0,
    'gameId' => 23,
    'roomId' => 1,
];

echo json_encode($changeBalancePayload, JSON_PRETTY_PRINT) . "\n\n";

// Show comparison format for debugging
echo "Debug Comparison Format:\n";
echo "----------------------------------------\n";
echo "Received UID: {$uid}\n";
echo "Expected UID: {$uid}\n";
echo "UID Match: ✅ YES\n\n";

echo "Received Token: " . substr($token, 0, 20) . "..." . substr($token, -10) . "\n";
echo "Expected Token: " . substr($token, 0, 20) . "..." . substr($token, -10) . "\n";
echo "Token Match: ✅ YES\n\n";

echo "========================================\n";
echo "Script completed!\n";
echo "========================================\n\n";

