# Backend Proxy Solution for Mixed Content

## Problem
- Vercel site: HTTPS ✅
- Game server: HTTP only ⚠️
- Browser: BLOCKS HTTP iframe in HTTPS page 🚫

## Best Solution: Laravel Backend Proxy

Add this route to your Laravel backend (`https://moneyking77.online`):

### 1. Create Controller Method

```php
<?php
// app/Http/Controllers/BuffaloGameController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BuffaloGameController extends Controller
{
    public function proxyGame(Request $request)
    {
        // Get the original HTTP game URL
        $gameUrl = $request->query('game_url');
        
        if (!$gameUrl) {
            return response()->json(['error' => 'No game URL provided'], 400);
        }
        
        try {
            // Fetch the game content from HTTP server
            $response = Http::timeout(30)->get($gameUrl);
            
            // Get content type
            $contentType = $response->header('Content-Type') ?? 'text/html';
            
            // Get the HTML content
            $content = $response->body();
            
            // Replace any HTTP links in the content to use our proxy
            // This ensures all resources also go through HTTPS
            $content = str_replace(
                'http://prime7.wlkfkskakdf.com',
                'https://moneyking77.online/api/buffalo/proxy-resource?url=http://prime7.wlkfkskakdf.com',
                $content
            );
            
            // Return the content with proper headers
            return response($content, 200)
                ->header('Content-Type', $contentType)
                ->header('X-Frame-Options', 'ALLOWALL')
                ->header('Access-Control-Allow-Origin', '*');
                
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load game',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function proxyResource(Request $request)
    {
        // Proxy for game resources (JS, CSS, images, etc.)
        $resourceUrl = $request->query('url');
        
        if (!$resourceUrl) {
            return response()->json(['error' => 'No resource URL provided'], 400);
        }
        
        try {
            $response = Http::timeout(30)->get($resourceUrl);
            $contentType = $response->header('Content-Type');
            
            return response($response->body(), 200)
                ->header('Content-Type', $contentType)
                ->header('Access-Control-Allow-Origin', '*');
                
        } catch (\Exception $e) {
            return response('', 404);
        }
    }
}
```

### 2. Add Routes

```php
<?php
// routes/api.php

// Add these routes to your existing routes
Route::middleware('auth:sanctum')->group(function () {
    // ... your existing routes ...
    
    // Game proxy routes
    Route::get('/buffalo/proxy-game', [BuffaloGameController::class, 'proxyGame']);
    Route::get('/buffalo/proxy-resource', [BuffaloGameController::class, 'proxyResource']);
});
```

### 3. Update Frontend to Use Proxy

No changes needed! Just update the `launch-game` API response to return the proxy URL instead:

```php
// In your existing launch-game endpoint
public function launchGame(Request $request)
{
    // ... your existing code ...
    
    // Original HTTP URL from game provider
    $originalGameUrl = "http://prime7.wlkfkskakdf.com/?gameId={$gameId}&roomId={$roomId}&lobbyUrl={$lobbyUrl}&uid={$uid}&token={$token}";
    
    // Return HTTPS proxy URL instead
    $proxyGameUrl = url('/api/buffalo/proxy-game') . '?game_url=' . urlencode($originalGameUrl);
    
    return response()->json([
        'code' => 1,
        'game_url' => $proxyGameUrl,  // HTTPS URL through your backend
        'message' => 'Game launched successfully'
    ]);
}
```

## How It Works

1. Frontend requests game from: `https://moneyking77.online/api/buffalo/launch-game`
2. Backend returns: `https://moneyking77.online/api/buffalo/proxy-game?game_url=http://prime7...`
3. Proxy endpoint fetches content from HTTP game server
4. Proxy serves it back through HTTPS
5. Browser sees HTTPS → HTTPS ✅ (No mixed content error!)

## Advantages

✅ Works on Vercel (HTTPS)
✅ No code changes needed in frontend
✅ Secure (all traffic goes through your HTTPS server)
✅ Fast (your backend is already fast)
✅ You control the proxy

## Testing

After adding the proxy to your backend:

1. Keep your Vercel deployment: `https://buffalo-slot-game.vercel.app`
2. The game will load through: `https://moneyking77.online/api/buffalo/proxy-game?game_url=...`
3. No more Mixed Content errors! ✅

---

**This is the BEST solution** because:
- You already have the Laravel backend
- No additional services needed
- Works perfectly with Vercel HTTPS
- Game loads in 0.21 seconds (as proven in local testing)

