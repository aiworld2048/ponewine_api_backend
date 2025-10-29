<?php

/*
 * ==============================================================================
 * UPDATED PROXY METHOD - Fixes "Connection Reset" Issue
 * ==============================================================================
 * 
 * The problem: Game resources (JS, CSS, images) have relative URLs that 
 * resolve to your Laravel backend instead of the game server.
 * 
 * The solution: Add a <base> tag to the HTML so all relative URLs point 
 * to the original game server.
 * 
 * REPLACE your existing proxyGame() method with this updated version:
 */

/**
 * Proxy Game Content - Fix HTTPS Mixed Content Error
 * Updated to handle game resources correctly
 */
public function proxyGame(Request $request)
{
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
        // Fetch the game content from HTTP server
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withOptions(['verify' => false])
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
        
        // If it's HTML, add a base tag so relative URLs work correctly
        if (strpos($contentType, 'text/html') !== false) {
            // Extract the base URL (domain) from the game URL
            $parsedUrl = parse_url($gameUrl);
            $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
            
            // Add base tag after <head> to fix relative URLs
            $baseTag = '<base href="' . $baseUrl . '/">';
            
            // Try to inject after <head> tag
            if (stripos($content, '<head>') !== false) {
                $content = preg_replace(
                    '/(<head[^>]*>)/i',
                    '$1' . $baseTag,
                    $content,
                    1
                );
            } elseif (stripos($content, '<html>') !== false) {
                // If no head tag, add one
                $content = preg_replace(
                    '/(<html[^>]*>)/i',
                    '$1<head>' . $baseTag . '</head>',
                    $content,
                    1
                );
            } else {
                // Last resort: add at the beginning
                $content = $baseTag . $content;
            }
            
            Log::info('Buffalo Proxy - Added base tag to HTML', [
                'url' => $gameUrl,
                'base_url' => $baseUrl
            ]);
        }
        
        Log::info('Buffalo Proxy - Successfully proxied game', [
            'url' => $gameUrl,
            'content_length' => strlen($content),
            'content_type' => $contentType
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
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'error' => 'Proxy error',
            'message' => $e->getMessage()
        ], 500);
    }
}

/*
 * ==============================================================================
 * WHAT THIS DOES
 * ==============================================================================
 * 
 * 1. Fetches the game HTML from the HTTP server
 * 2. Adds a <base href="http://prime7.wlkfkskakdf.com/"> tag to the HTML
 * 3. This makes all relative URLs (JS, CSS, images) load from the game server
 * 4. Returns the modified HTML through HTTPS
 * 
 * Example:
 * - Game HTML has: <script src="/game.js">
 * - Without base tag: Loads from https://moneyking77.online/game.js (404 error)
 * - With base tag: Loads from http://prime7.wlkfkskakdf.com/game.js (works!)
 * 
 * ==============================================================================
 * HOW TO UPDATE
 * ==============================================================================
 * 
 * 1. Open: App\Http\Controllers\Api\V1\Game\BuffaloGameController.php
 * 2. Find your existing proxyGame() method
 * 3. Replace the entire method with the code above
 * 4. Deploy:
 *    php artisan route:clear
 *    git push
 * 
 * ==============================================================================
 */

