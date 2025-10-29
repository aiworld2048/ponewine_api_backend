<?php

/*
 * ==============================================================================
 * ADD THIS METHOD TO YOUR EXISTING BuffaloGameController
 * ==============================================================================
 * 
 * File location: App\Http\Controllers\Api\V1\Game\BuffaloGameController.php
 * 
 * Simply add this method at the end of your class (before the closing brace)
 * This is the ONLY code you need to add!
 */

/**
 * Proxy Game Content - Fix HTTPS Mixed Content Error
 * This allows the HTTP game to work on HTTPS frontend (Vercel)
 * 
 * Endpoint: GET /api/buffalo/proxy-game?url=...
 */
public function proxyGame(Request $request)
{
    // Get the game URL from query parameter
    $gameUrl = $request->query('url');
    
    if (!$gameUrl) {
        return response()->json([
            'error' => 'No URL provided',
            'message' => 'Please provide url parameter'
        ], 400);
    }
    
    // Validate it's the expected game server for security
    if (!str_starts_with($gameUrl, 'http://prime7.wlkfkskakdf.com')) {
        return response()->json([
            'error' => 'Invalid URL',
            'message' => 'Only Buffalo game server URLs are allowed'
        ], 403);
    }
    
    try {
        // Fetch the game content from HTTP server (server-side, no mixed content)
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withOptions(['verify' => false]) // In case of SSL issues
            ->get($gameUrl);
        
        if (!$response->successful()) {
            Log::error('Buffalo Proxy - Failed to fetch game', [
                'url' => $gameUrl,
                'status' => $response->status()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch game',
                'status' => $response->status()
            ], $response->status());
        }
        
        // Get content
        $content = $response->body();
        $contentType = $response->header('Content-Type') ?? 'text/html';
        
        Log::info('Buffalo Proxy - Successfully proxied game', [
            'url' => $gameUrl,
            'content_length' => strlen($content)
        ]);
        
        // Return the game content with headers that allow iframe embedding
        return response($content, 200)
            ->header('Content-Type', $contentType)
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Content-Security-Policy', 'frame-ancestors *')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', '*')
            ->header('Access-Control-Allow-Headers', '*');
            
    } catch (\Exception $e) {
        Log::error('Buffalo Proxy - Error', [
            'url' => $gameUrl,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'error' => 'Proxy error',
            'message' => $e->getMessage()
        ], 500);
    }
}

/*
 * ==============================================================================
 * WHERE TO ADD THIS IN YOUR FILE
 * ==============================================================================
 * 
 * Open: App\Http\Controllers\Api\V1\Game\BuffaloGameController.php
 * 
 * Scroll to the bottom, find the closing brace of your launchGame() method
 * 
 * Add the proxyGame() method above the final closing brace:
 * 
 * class BuffaloGameController extends Controller
 * {
 *     // ... your existing methods ...
 *     
 *     public function launchGame(Request $request)
 *     {
 *         // ... your existing code ...
 *     }
 *     
 *     // ADD THE PROXY METHOD HERE ↓↓↓
 *     public function proxyGame(Request $request)
 *     {
 *         // ... copy the code from above ...
 *     }
 *     
 * } // <- closing brace of class
 * 
 */

