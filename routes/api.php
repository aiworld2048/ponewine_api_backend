<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Shan\ShanGetBalanceController;
use App\Http\Controllers\Api\PoneWine\PoneWineClientBalanceUpdateController;
use App\Http\Controllers\Api\PoneWine\PoneWineLaunchGameController;
use App\Http\Controllers\Api\PoneWine\ProviderLaunchGameController;
use App\Http\Controllers\Api\V1\Auth\AuthController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/player-change-password', [AuthController::class, 'playerChangePassword']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::group(['prefix' => 'shan'], function () {
    Route::post('balance', [ShanGetBalanceController::class, 'getBalance']);
    
});



// Provider route
Route::post('/provider/launch-game', [ProviderLaunchGameController::class, 'launchGameForClient']);

// shan route end
// PoneWine 
Route::post('/pone-wine/client-report', [PoneWineClientBalanceUpdateController::class, 'PoneWineClientReport']);


Route::middleware(['auth:sanctum'])->group(function () {
    // route prefix shan 
    Route::group(['prefix' => 'ponewine'], function () {
       Route::post('/pone-wine/launch-game', [PoneWineLaunchGameController::class, 'launchGame']);
    });
});


