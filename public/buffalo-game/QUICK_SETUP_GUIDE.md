# 🚀 Quick Setup Guide - Fix Vercel HTTPS Issue

## The Problem
- ✅ Local testing: Works perfectly (0.22 seconds)
- ❌ Vercel (HTTPS): Blocked by browser (Mixed Content error)
- ❌ Public CORS proxies: Block iframe embedding

## The ONLY Solution: Backend Proxy

Your Laravel backend (`https://moneyking77.online`) needs to proxy the HTTP game through HTTPS.

---

## 📋 Step-by-Step Implementation

### Step 1: Add Controller to Laravel Backend

Create file: `app/Http/Controllers/BuffaloProxyController.php`

Copy the entire code from **`LARAVEL_BACKEND_CODE.php`** (I created this file for you)

Or create it manually:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BuffaloProxyController extends Controller
{
    public function proxyGame(Request $request)
    {
        $gameUrl = $request->query('url');
        
        if (!$gameUrl) {
            return response()->json(['error' => 'No URL provided'], 400);
        }
        
        if (!str_starts_with($gameUrl, 'http://prime7.wlkfkskakdf.com')) {
            return response()->json(['error' => 'Invalid URL'], 403);
        }
        
        try {
            $response = Http::timeout(30)->get($gameUrl);
            
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
}
```

### Step 2: Add Route

Edit `routes/api.php` and add:

```php
use App\Http\Controllers\BuffaloProxyController;

// Add this route
Route::get('/buffalo/proxy-game', [BuffaloProxyController::class, 'proxyGame']);
```

### Step 3: Deploy Backend

```bash
# On your Laravel backend server
git add .
git commit -m "Add game proxy for HTTPS support"
git push
```

### Step 4: Test Backend Proxy

Visit this URL in your browser:
```
https://moneyking77.online/api/buffalo/proxy-game?url=http://prime7.wlkfkskakdf.com
```

You should see content (not an error)!

### Step 5: Deploy Frontend to Vercel

```bash
# In your buffalo-game folder
git add .
git commit -m "Use backend proxy for HTTPS"
git push
```

### Step 6: Test on Vercel ✅

Visit: `https://buffalo-slot-game.vercel.app`

Your game should now:
- ✅ Load in ~3 seconds
- ✅ No Mixed Content errors
- ✅ Work perfectly on HTTPS

---

## 🔍 Troubleshooting

### If you see "No URL provided" error:

The backend route is working! The frontend is configured correctly.

### If you see 403 "Invalid URL":

The proxy is working but blocking the URL. Check the domain validation in the controller.

### If you see 500 error:

Check Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### If you see CORS errors:

Make sure the `Access-Control-Allow-Origin: *` header is set in the response.

---

## ✅ What This Does

```
Before (Doesn't Work):
Browser (HTTPS) → Game Server (HTTP) ❌ BLOCKED

After (Works):
Browser (HTTPS) → Laravel Backend (HTTPS) → Game Server (HTTP) ✅ SUCCESS
```

The backend fetches the HTTP game server-side (no browser blocking), then serves it through HTTPS!

---

## 📊 Expected Performance

- **Local (HTTP)**: 0.21 seconds
- **Vercel with Proxy (HTTPS)**: 2-3 seconds
- **No errors**: Clean console
- **All features work**: Balance updates, game play, etc.

---

## 💡 Alternative: Just Use HTTP Hosting

If you don't want to implement the backend proxy:

1. Deploy to HTTP hosting instead of Vercel
2. Use the `start-local-server.bat` for local testing
3. Deploy to a hosting service that allows HTTP

But the **backend proxy is the best solution** for production HTTPS!

