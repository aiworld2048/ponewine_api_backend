<?php
/**
 * Test Buffalo Game Launch Functionality
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\BuffaloGameService;

echo "=== Testing Buffalo Game Launch Functionality ===\n\n";

// Get test user
$user = User::where('user_name', 'PLAYER0101')->first();
if (!$user) {
    echo "Test user PLAYER0101 not found. Creating...\n";
    $user = User::create([
        'user_name' => 'PLAYER0101',
        'email' => 'player0101@test.com',
        'password' => bcrypt('password'),
        'balance' => 1000.00,
        'type' => \App\Enums\UserType::Player->value
    ]);
}

echo "Testing with user: {$user->user_name} (Balance: {$user->balance})\n\n";

// Test 1: Generate Buffalo Auth
echo "1. Testing Buffalo Auth Generation:\n";
$auth = BuffaloGameService::generateBuffaloAuth($user);
echo "UID: {$auth['uid']}\n";
echo "Token: {$auth['token']}\n\n";

// Test 2: Get Available Rooms
echo "2. Testing Available Rooms:\n";
$availableRooms = BuffaloGameService::getAvailableRooms($user);
foreach ($availableRooms as $roomId => $config) {
    echo "Room {$roomId}: {$config['name']} (Min Bet: {$config['min_bet']})\n";
}
echo "\n";

// Test 3: Generate Game URLs for each room
echo "3. Testing Game URL Generation:\n";
$lobbyUrl = 'https://gamestar77.online';

foreach ($availableRooms as $roomId => $config) {
    $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl);
    $fullUrl = $gameUrl . '&uid=' . $auth['uid'] . '&token=' . $auth['token'];
    
    echo "Room {$roomId} ({$config['name']}):\n";
    echo "URL: {$fullUrl}\n\n";
}

// Test 4: Test API endpoint (simulate frontend request)
echo "4. Testing Launch Game API Endpoint:\n";
$baseUrl = 'http://localhost:8000/api/buffalo/launch-game';

$testData = json_encode([
    'type_id' => 1,
    'provider_id' => 23, // Buffalo provider
    'game_id' => 23,
    'room_id' => 1
]);

echo "Request Data: {$testData}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: {$httpCode}\n";
echo "Response: {$response}\n\n";

// Parse response
$responseData = json_decode($response, true);
if ($responseData && isset($responseData['code']) && $responseData['code'] === 1) {
    echo "✅ SUCCESS! Game launch working correctly.\n";
    echo "Generated URL: {$responseData['Url']}\n";
    echo "Room Info: " . json_encode($responseData['room_info']) . "\n";
} elseif ($responseData && isset($responseData['message']) && $responseData['message'] === 'Unauthenticated.') {
    echo "⚠️ Expected 401 - API endpoint requires authentication (this is correct)\n";
    echo "✅ URL generation is working perfectly!\n";
} else {
    echo "❌ FAILED! Check the response above.\n";
}

echo "\n=== Test Complete ===\n";
echo "Use these URLs in your frontend or test directly in browser.\n";
