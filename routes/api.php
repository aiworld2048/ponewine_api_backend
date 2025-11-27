<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PoneWine\PoneWineClientBalanceUpdateController;
use App\Http\Controllers\Api\PoneWine\PoneWineLaunchGameController;
//use App\Http\Controllers\Api\PoneWine\ProviderLaunchGameController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Game\BuffaloGameController;

use App\Http\Controllers\Api\V1\Shan\ShanGetBalanceController;
use App\Http\Controllers\Api\V1\Game\ProviderLaunchGameController;
use App\Http\Controllers\Api\V1\Shan\ShanLaunchGameController;
use App\Http\Controllers\Api\V1\Shan\ShanTransactionController;
use App\Http\Controllers\Api\V1\Shan\BalanceUpdateCallbackController;
use App\Http\Controllers\Api\V1\Game\buffalo_multi\BuffaloGameMultiSiteController;



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/player-change-password', [AuthController::class, 'playerChangePassword']);
Route::post('/logout', [AuthController::class, 'logout']);


// PoneWine 
// Route::post('/pone-wine/client-report', [PoneWineClientBalanceUpdateController::class, 'PoneWineClientReport']);


// Route::middleware(['auth:sanctum'])->group(function () {
//     // route prefix shan 
//     Route::group(['prefix' => 'ponewine'], function () {
//        Route::post('/pone-wine/launch-game', [PoneWineLaunchGameController::class, 'launchGame']);
//     });
// });


// Buffalo Game API routes
Route::prefix('buffalo')->group(function () {
    // Public webhook endpoints (no authentication required)
    Route::post('/get-user-balance', [BuffaloGameMultiSiteController::class, 'getUserBalance']);
    Route::post('/change-balance', [BuffaloGameMultiSiteController::class, 'changeBalance']);
    Route::post('/launch-game', [BuffaloGameMultiSiteController::class, 'launchGame']);
    
    // Protected endpoints for frontend integration
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/game-auth', [BuffaloGameMultiSiteController::class, 'generateGameAuth']);
        Route::post('/game-url', [BuffaloGameMultiSiteController::class, 'generateGameUrl']);
        
    });

    //  Route::get('/proxy-game', [BuffaloGameController::class, 'proxyGame']);
});

// Buffalo Game Proxy Routes (NO AUTH - called from game iframe)
Route::get('/buffalo/proxy-game', [BuffaloGameController::class, 'proxyGame']);
Route::get('/buffalo/proxy-resource', [BuffaloGameController::class, 'proxyResource']);


// shan route start
Route::post('/transactions', [ShanTransactionController::class, 'ShanTransactionCreate'])->middleware('transaction');

Route::group(['prefix' => 'shan'], function () {
    Route::post('balance', [ShanGetBalanceController::class, 'getBalance']);
    Route::post('/client/balance-update', [BalanceUpdateCallbackController::class, 'handleBalanceUpdate']); 
});

Route::middleware(['auth:sanctum'])->group(function () {
    // route prefix shan 
    Route::group(['prefix' => 'shankomee'], function () {
        Route::post('launch-game', [ShanLaunchGameController::class, 'launchGame']);
    });
});

// Provider route
Route::post('/provider/launch-game', [ProviderLaunchGameController::class, 'launchGameForClient']);

// shan route end





