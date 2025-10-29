<?php

/*
 * ==============================================================================
 * FINAL ROUTES - Add BOTH proxy routes
 * ==============================================================================
 * 
 * File: routes/api.php
 * 
 * You need TWO routes (both OUTSIDE auth middleware):
 */

// Buffalo Game Proxy Routes (NO AUTH - called from game iframe)
Route::get('/buffalo/proxy-game', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyGame']);
Route::get('/buffalo/proxy-resource', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyResource']);

// Your other Buffalo routes (WITH AUTH)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/buffalo/game-auth', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'generateGameAuth']);
    Route::post('/buffalo/launch-game', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'launchGame']);
});

/*
 * ==============================================================================
 * OR with prefix (cleaner):
 * ==============================================================================
 */

Route::prefix('buffalo')->group(function () {
    // Public routes (no auth)
    Route::post('/get-user-balance', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'getUserBalance']);
    Route::post('/change-balance', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'changeBalance']);
    Route::get('/proxy-game', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyGame']);
    Route::get('/proxy-resource', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyResource']);
    
    // Protected routes (with auth)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/game-auth', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'generateGameAuth']);
        Route::post('/launch-game', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'launchGame']);
    });
});

/*
 * ==============================================================================
 * VERIFY ROUTES
 * ==============================================================================
 * 
 * After adding, run:
 *   php artisan route:list | grep buffalo
 * 
 * You should see:
 *   GET|HEAD  api/buffalo/proxy-game ............ (no middleware)
 *   GET|HEAD  api/buffalo/proxy-resource ........ (no middleware)
 *   GET|HEAD  api/buffalo/game-auth ............. auth:sanctum
 *   POST      api/buffalo/launch-game ........... auth:sanctum
 * 
 * ==============================================================================
 */

