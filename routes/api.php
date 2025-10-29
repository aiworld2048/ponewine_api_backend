<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Shan\ShanGetBalanceController;
use App\Http\Controllers\Api\PoneWine\PoneWineClientBalanceUpdateController;
use App\Http\Controllers\Api\PoneWine\PoneWineLaunchGameController;
use App\Http\Controllers\Api\PoneWine\ProviderLaunchGameController;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Game\BuffaloGameController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/player-change-password', [AuthController::class, 'playerChangePassword']);
Route::post('/logout', [AuthController::class, 'logout']);

// Route::group(['prefix' => 'shan'], function () {
//     Route::post('balance', [ShanGetBalanceController::class, 'getBalance']);
    
// });



// Provider route
// Route::post('/provider/launch-game', [ProviderLaunchGameController::class, 'launchGameForClient']);

// shan route end
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
    Route::post('/get-user-balance', [BuffaloGameController::class, 'getUserBalance']);
    Route::post('/change-balance', [BuffaloGameController::class, 'changeBalance']);
    
    // Protected endpoints for frontend integration
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/game-auth', [BuffaloGameController::class, 'generateGameAuth']);
        Route::post('/game-url', [BuffaloGameController::class, 'generateGameUrl']);
        Route::post('/launch-game', [BuffaloGameController::class, 'launchGame']);
        // Route::get('/proxy-game', [BuffaloGameController::class, 'proxyGame']);
    });

    //  Route::get('/proxy-game', [BuffaloGameController::class, 'proxyGame']);
});

// Buffalo Game Proxy Routes (NO AUTH - called from game iframe)
Route::get('/buffalo/proxy-game', [BuffaloGameController::class, 'proxyGame']);
Route::get('/buffalo/proxy-resource', [BuffaloGameController::class, 'proxyResource']);





