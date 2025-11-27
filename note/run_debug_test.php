<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "=== DEBUGGING BALANCE ISSUE - ENHANCED LOGGING ===\n\n";

// Check current user balances
$players = User::whereIn('user_name', ['PLAYER0101', 'PLAYER0201'])->get();

echo "Current Player Balances:\n";
foreach ($players as $player) {
    echo "Player: {$player->user_name}\n";
    echo "  Raw Balance: {$player->balance}\n";
    echo "  Balance Type: " . gettype($player->balance) . "\n";
    echo "  JSON Encoded: " . json_encode($player->balance) . "\n\n";
}

echo "=== ENHANCED DEBUG LOGGING IS NOW ENABLED ===\n\n";
echo "The debug logs will now show:\n";
echo "1. ✅ Incoming request data\n";
echo "2. ✅ User balance lookups\n";
echo "3. ✅ Currency conversion calculations\n";
echo "4. ✅ Balance formatting steps\n";
echo "5. ✅ Transaction processing details\n";
echo "6. ✅ Before/after balance values\n";
echo "7. ✅ Final response values\n\n";

echo "🔍 KEY FINDINGS FROM PREVIOUS LOGS:\n";
echo "- GetBalanceController: Working correctly (IDR=50000, IDR2=50)\n";
echo "- Balance calculations: Mathematically correct\n";
echo "- Issue: Missing transaction processing logs in Deposit/Withdraw\n";
echo "- Likely cause: Gaming provider expects different balance format\n\n";

echo "📋 NEXT STEPS:\n";
echo "1. Run your API tests again\n";
echo "2. Check storage/logs/laravel.log for complete transaction flow\n";
echo "3. Look for 'processTransactions completed' and 'Final API response' logs\n";
echo "4. Compare our response format with working site expectations\n\n";

echo "🎯 EXPECTED DEBUG OUTPUT:\n";
echo "Look for these new log entries:\n";
echo "- 'DepositController: Starting processTransactions'\n";
echo "- 'DepositController: processTransactions completed'\n";
echo "- 'WithdrawController: Starting processWithdrawTransactions'\n";
echo "- 'WithdrawController: processWithdrawTransactions completed'\n";
echo "- 'Final API response' with complete response data\n\n";

echo "This will help us identify if the issue is:\n";
echo "A) Our API response format\n";
echo "B) Gaming provider validation logic\n";
echo "C) Balance synchronization between systems\n\n";
