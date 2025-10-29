<?php

/*
 * ==============================================================================
 * ADD THIS ROUTE TO YOUR routes/api.php
 * ==============================================================================
 * 
 * Find where you have your Buffalo game routes and add this ONE line:
 */

// Add this line with your other Buffalo routes
Route::get('/buffalo/proxy-game', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyGame']);

/*
 * ==============================================================================
 * EXAMPLE - Your routes/api.php might look like this:
 * ==============================================================================
 */

/*

use App\Http\Controllers\Api\V1\Game\BuffaloGameController;

Route::prefix('buffalo')->group(function () {
    // Your existing routes
    Route::post('/get-user-balance', [BuffaloGameController::class, 'getUserBalance']);
    Route::post('/change-balance', [BuffaloGameController::class, 'changeBalance']);
    
    // Protected routes (with auth)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/game-auth', [BuffaloGameController::class, 'generateGameAuth']);
        Route::post('/game-url', [BuffaloGameController::class, 'generateGameUrl']);
        Route::post('/launch-game', [BuffaloGameController::class, 'launchGame']);
        
        // ADD THIS NEW ROUTE ↓↓↓
        Route::get('/proxy-game', [BuffaloGameController::class, 'proxyGame']);
    });
});

*/

/*
 * ==============================================================================
 * OR SIMPLER VERSION (if you prefer):
 * ==============================================================================
 * 
 * Just add this anywhere in your routes/api.php:
 */

// Route::get('/buffalo/proxy-game', [\App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyGame']);

/*
 * ==============================================================================
 * CRITICAL: DO NOT PUT THIS ROUTE INSIDE auth:sanctum MIDDLEWARE!
 * ==============================================================================
 * 
 * ❌ WRONG:
 * Route::middleware('auth:sanctum')->group(function () {
 *     Route::get('/buffalo/proxy-game', [...]); // This will cause 500 error!
 * });
 * 
 * ✅ CORRECT:
 * Route::get('/buffalo/proxy-game', [...]);  // Outside auth middleware
 * 
 * WHY: The game iframe doesn't have the user's auth token, so it can't 
 * authenticate. The proxy is safe without auth because it only fetches 
 * from the validated game server URL.
 * 
 * ==============================================================================
 * IMPORTANT NOTES:
 * ==============================================================================
 * 
 * 1. The proxy route does NOT need authentication (auth:sanctum)
 *    Because it's called from the game iframe which doesn't have the token
 * 
 * 2. The route path MUST be exactly: /buffalo/proxy-game
 *    Because your frontend is already configured to use:
 *    https://moneyking77.online/api/buffalo/proxy-game?url=...
 * 
 * 3. After adding the route, clear cache:
 *    php artisan route:clear
 *    php artisan cache:clear
 * 
 * 4. Verify the route has NO auth middleware:
 *    php artisan route:list | grep proxy-game
 *    Should show: GET|HEAD  api/buffalo/proxy-game (no middleware shown)
 */

