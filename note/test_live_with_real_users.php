<?php
/**
 * Test Live Server with All Real Users
 */

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\BuffaloGameService;

echo "=== Testing Live Server with All Real Users ===\n\n";

$users = User::where('type', 40)->get(); // Get all players

$baseUrl = 'https://gamestar77.online/api/buffalo/get-user-balance';

foreach ($users as $user) {
    echo "Testing User: {$user->user_name} (ID: {$user->id})\n";
    
    // Method 1: Use BuffaloGameService (same as local)
    try {
        $auth = BuffaloGameService::generateBuffaloAuth($user);
        $uid = $auth['uid'];
        $token = $auth['token'];
        
        echo "  BuffaloGameService UID: {$uid}\n";
        echo "  BuffaloGameService Token: {$token}\n";
        
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
        
        echo "  Response: {$response}\n";
        
        if (strpos($response, '"code":1') !== false) {
            echo "  🎉 SUCCESS with BuffaloGameService!\n";
            echo "  Use these values for Postman:\n";
            echo "  UID: {$uid}\n";
            echo "  Token: {$token}\n";
            break;
        }
        
        // Method 2: Try simple hash approach
        $simpleUid = 'gam' . substr(md5($user->user_name), 0, 28);
        $simpleToken = hash('sha256', $user->user_name . 'gamestar77.online' . $user->id);
        
        echo "  Simple UID: {$simpleUid}\n";
        echo "  Simple Token: {$simpleToken}\n";
        
        $testData2 = json_encode([
            'uid' => $simpleUid,
            'token' => $simpleToken
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
        
        echo "  Simple Response: {$response2}\n";
        
        if (strpos($response2, '"code":1') !== false) {
            echo "  🎉 SUCCESS with Simple Hash!\n";
            echo "  Use these values for Postman:\n";
            echo "  UID: {$simpleUid}\n";
            echo "  Token: {$simpleToken}\n";
            break;
        }
        
    } catch (Exception $e) {
        echo "  Error: {$e->getMessage()}\n";
    }
    
    echo "\n";
}

echo "=== Test Complete ===\n";
