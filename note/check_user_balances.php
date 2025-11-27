<?php

// Simple script to check user balances
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Enums\UserType;

echo "=== User Balance Check ===\n\n";

try {
    // Get all users with their balances
    $users = User::select('id', 'name', 'user_name', 'type', 'balance', 'created_at')
        ->orderBy('type')
        ->orderBy('id')
        ->get();

    if ($users->isEmpty()) {
        echo "❌ No users found in database.\n";
        echo "You may need to run the seeder first:\n";
        echo "php artisan db:seed --class=UsersTableSeeder\n\n";
        exit;
    }

    echo "📊 Found {$users->count()} users:\n\n";

    $totalBalance = 0;
    $typeCounts = [];

    foreach ($users as $user) {
        $typeName = UserType::from($user->type)->name;
        $balance = number_format($user->balance, 2);
        
        echo "ID: {$user->id} | {$typeName} | {$user->user_name} | Balance: {$balance}\n";
        
        $totalBalance += $user->balance;
        $typeCounts[$typeName] = ($typeCounts[$typeName] ?? 0) + 1;
    }

    echo "\n=== Summary ===\n";
    echo "Total Users: {$users->count()}\n";
    echo "Total Balance: " . number_format($totalBalance, 2) . "\n";
    echo "Average Balance: " . number_format($totalBalance / $users->count(), 2) . "\n\n";

    echo "Users by Type:\n";
    foreach ($typeCounts as $type => $count) {
        echo "- {$type}: {$count} users\n";
    }

    echo "\n=== Balance Distribution ===\n";
    $owner = $users->where('type', UserType::Owner->value)->first();
    $master = $users->where('type', UserType::Master->value)->first();
    $agents = $users->where('type', UserType::Agent->value);
    $players = $users->where('type', UserType::Player->value);

    if ($owner) {
        echo "Owner ({$owner->user_name}): " . number_format($owner->balance, 2) . "\n";
    }
    if ($master) {
        echo "Master ({$master->user_name}): " . number_format($master->balance, 2) . "\n";
    }
    echo "Agents: " . $agents->count() . " users, Total: " . number_format($agents->sum('balance'), 2) . "\n";
    echo "Players: " . $players->count() . " users, Total: " . number_format($players->sum('balance'), 2) . "\n";

    echo "\n✅ Balance check completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
