# 🎯 Simple Steps - Add Proxy to Your Existing Backend

You already have the Buffalo game backend! You just need to add **ONE method** and **ONE route**.

---

## Step 1: Add Method to Controller (2 minutes)

### File: `App\Http\Controllers\Api\V1\Game\BuffaloGameController.php`

1. Open your `BuffaloGameController.php` file
2. Scroll to the bottom (after your `launchGame()` method)
3. **Copy and paste** this entire method before the final closing brace `}`:

```php
/**
 * Proxy Game Content - Fix HTTPS Mixed Content Error
 */
public function proxyGame(Request $request)
{
    $gameUrl = $request->query('url');
    
    if (!$gameUrl || !str_starts_with($gameUrl, 'http://prime7.wlkfkskakdf.com')) {
        return response()->json(['error' => 'Invalid URL'], 403);
    }
    
    try {
        $response = \Illuminate\Support\Facades\Http::timeout(30)->get($gameUrl);
        
        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to fetch game'], 500);
        }
        
        $content = $response->body();
        $contentType = $response->header('Content-Type') ?? 'text/html';
        
        return response($content, 200)
            ->header('Content-Type', $contentType)
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Content-Security-Policy', 'frame-ancestors *')
            ->header('Access-Control-Allow-Origin', '*');
            
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
```

**Your controller will look like this:**

```php
class BuffaloGameController extends Controller
{
    // ... all your existing methods ...
    
    public function launchGame(Request $request)
    {
        // ... your existing code ...
    }
    
    // ADD THIS NEW METHOD HERE ↓
    public function proxyGame(Request $request)
    {
        // ... the code above ...
    }
    
} // <- closing brace
```

---

## Step 2: Add Route (1 minute)

### File: `routes/api.php`

Find your Buffalo game routes and add this **ONE line**:

```php
Route::get('/buffalo/proxy-game', [App\Http\Controllers\Api\V1\Game\BuffaloGameController::class, 'proxyGame']);
```

**Example - Your routes might look like:**

```php
Route::prefix('buffalo')->group(function () {
    Route::post('/get-user-balance', [BuffaloGameController::class, 'getUserBalance']);
    Route::post('/change-balance', [BuffaloGameController::class, 'changeBalance']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/game-auth', [BuffaloGameController::class, 'generateGameAuth']);
        Route::post('/launch-game', [BuffaloGameController::class, 'launchGame']);
        
        // ADD THIS LINE ↓
        Route::get('/proxy-game', [BuffaloGameController::class, 'proxyGame']);
    });
});
```

**⚠️ CRITICAL**: The proxy route MUST be OUTSIDE `auth:sanctum` middleware!

**WRONG:**
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/buffalo/proxy-game', [...]); // ❌ Will cause 500 error!
});
```

**CORRECT:**
```php
// Proxy route - NO AUTH (iframe can't authenticate)
Route::get('/buffalo/proxy-game', [BuffaloGameController::class, 'proxyGame']);

// Other routes - WITH AUTH
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/buffalo/launch-game', [BuffaloGameController::class, 'launchGame']);
});
```

---

## Step 3: Deploy Backend (1 minute)

```bash
# Clear cache
php artisan route:clear
php artisan cache:clear

# Commit and deploy
git add .
git commit -m "Add Buffalo game proxy for HTTPS support"
git push
```

---

## Step 4: Test Backend (30 seconds)

Open this URL in your browser:

```
https://moneyking77.online/api/buffalo/proxy-game?url=http://prime7.wlkfkskakdf.com
```

**Expected**: You should see HTML content (the game page)  
**Error**: If you see JSON error, check the controller and route

---

## Step 5: Deploy Frontend (Already Done!) ✅

Your frontend code is already updated and ready! Just push to Vercel:

```bash
cd buffalo-game
git add .
git commit -m "Ready for backend proxy"
git push
```

---

## Step 6: Test on Vercel 🎉

Visit: `https://buffalo-slot-game.vercel.app`

Login and launch a game. It should now:
- ✅ Load in ~3 seconds
- ✅ No Mixed Content errors
- ✅ Work perfectly on HTTPS

---

## 🔍 Troubleshooting

### If you get "Route not found":
```bash
php artisan route:list | grep proxy
```
You should see: `GET|HEAD  api/buffalo/proxy-game`

### If you get "Method not found":
Check that you added the `proxyGame()` method to the controller

### If you get CORS errors:
The response headers in the proxy method should fix this

---

## ✅ That's It!

You're just adding:
- ✅ 1 method (30 lines of code)
- ✅ 1 route (1 line of code)

Total time: **5 minutes**

Your game will work perfectly on Vercel HTTPS! 🚀

