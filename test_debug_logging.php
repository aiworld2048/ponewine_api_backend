<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== TESTING DEBUG LOGGING FOR BALANCE ISSUE ===\n\n";

// Check current user balances
$players = User::whereIn('user_name', ['PLAYER0101', 'PLAYER0102'])->get();

echo "Current Player Balances:\n";
foreach ($players as $player) {
    echo "Player: {$player->user_name}\n";
    echo "  Raw Balance: {$player->balance}\n";
    echo "  Balance Type: " . gettype($player->balance) . "\n";
    echo "  JSON Encoded: " . json_encode($player->balance) . "\n\n";
}

echo "=== DEBUG LOGGING IS NOW ENABLED ===\n";
echo "Now run your API tests and check the Laravel log file:\n";
echo "storage/logs/laravel.log\n\n";
echo "Look for entries starting with:\n";
echo "- '=== DEBUGGING BALANCE ISSUE - GetBalanceController ==='\n";
echo "- '=== DEBUGGING BALANCE ISSUE - DepositController ==='\n";
echo "- '=== DEBUGGING BALANCE ISSUE - WithdrawController ==='\n\n";
echo "The debug logs will show:\n";
echo "1. Incoming request data\n";
echo "2. User balance lookups\n";
echo "3. Currency conversion calculations\n";
echo "4. Balance formatting steps\n";
echo "5. Final response values\n\n";
echo "This will help us identify exactly where the 'Balance is incorrect' issue occurs.\n";
