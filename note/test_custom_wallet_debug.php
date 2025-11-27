<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Services\CustomWalletService;
use App\Enums\TransactionName;

echo "=== TESTING CUSTOM WALLET SERVICE DEBUG ===\n\n";

// Check current user balances
$players = User::whereIn('user_name', ['PLAYER0101', 'PLAYER0201'])->get();

echo "Current Player Balances:\n";
foreach ($players as $player) {
    echo "Player: {$player->user_name}\n";
    echo "  Raw Balance: {$player->balance}\n";
    echo "  Balance Type: " . gettype($player->balance) . "\n\n";
}

echo "=== CUSTOM WALLET SERVICE DEBUG LOGGING IS NOW ENABLED ===\n\n";
echo "The debug logs will now show:\n";
echo "1. ✅ CustomWalletService::deposit - Before update\n";
echo "2. ✅ CustomWalletService::deposit - After update\n";
echo "3. ✅ CustomWalletService::withdraw - Before update\n";
echo "4. ✅ CustomWalletService::withdraw - After update\n";
echo "5. ✅ Expected vs Actual balance comparison\n";
echo "6. ✅ Update success verification\n\n";

echo "🔍 KEY FINDINGS FROM PREVIOUS LOGS:\n";
echo "- WithdrawController: before_balance=50000, balance=50000 (should be 49990)\n";
echo "- DepositController: before_balance=49990, balance=49990 (should be 50000)\n";
echo "- Issue: Balance updates are not being applied to the database\n\n";

echo "📋 NEXT STEPS:\n";
echo "1. Run your API tests again\n";
echo "2. Check storage/logs/laravel.log for CustomWalletService debug output\n";
echo "3. Look for 'CustomWalletService::deposit/withdraw - Before/After update' logs\n";
echo "4. Check if 'update_successful' is true or false\n\n";

echo "🎯 EXPECTED DEBUG OUTPUT:\n";
echo "Look for these new log entries:\n";
echo "- 'CustomWalletService::withdraw - Before update' with old_balance=50000, amount=10, new_balance=49990\n";
echo "- 'CustomWalletService::withdraw - After update' with expected_new_balance=49990, actual_new_balance=?\n";
echo "- 'CustomWalletService::deposit - Before update' with old_balance=49990, amount=10, new_balance=50000\n";
echo "- 'CustomWalletService::deposit - After update' with expected_new_balance=50000, actual_new_balance=?\n\n";

echo "This will help us identify if the issue is:\n";
echo "A) Database update not working\n";
echo "B) Model refresh not working\n";
echo "C) Balance field not being updated\n";
echo "D) Transaction rollback issue\n\n";
