<?php
/**
 * Check Users in Database
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== Users in Database ===\n\n";

$users = User::select('id', 'user_name', 'type', 'balance')->get();

if ($users->count() == 0) {
    echo "No users found in database.\n";
    echo "Run: php artisan db:seed --class=UsersTableSeeder\n";
} else {
    echo "Found " . $users->count() . " users:\n\n";
    
    foreach ($users as $user) {
        echo "ID: {$user->id} | Username: {$user->user_name} | Type: {$user->type} | Balance: {$user->balance}\n";
    }
    
    echo "\n=== Testing Buffalo API with Real Users ===\n\n";
    
    // Test with the first player user
    $player = $users->where('type', 40)->first(); // Player type is 40
    
    if ($player) {
        echo "Testing with Player: {$player->user_name}\n";
        
        // Generate UID and token for this player
        $uid = 'gam' . substr(md5($player->user_name), 0, 28);
        $token = hash('sha256', $player->user_name . 'gamestar77.online' . $player->id);
        
        echo "Generated UID: {$uid}\n";
        echo "Generated Token: {$token}\n\n";
        
        // Test with live server
        $baseUrl = 'https://gamestar77.online/api/buffalo/get-user-balance';
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
        
        echo "Live Server Test:\n";
        echo "Response: {$response}\n";
        
        if (strpos($response, '"code":1') !== false) {
            echo "🎉 SUCCESS! Use these values for Postman:\n";
            echo "UID: {$uid}\n";
            echo "Token: {$token}\n";
        }
    } else {
        echo "No player users found.\n";
    }
}

echo "\n=== Test Complete ===\n";
