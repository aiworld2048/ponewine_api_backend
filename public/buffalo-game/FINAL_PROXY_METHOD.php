<?php

/*
 * ==============================================================================
 * FINAL PROXY METHOD - Rewrites ALL HTTP URLs to HTTPS
 * ==============================================================================
 * 
 * The issue: Game HTML has absolute HTTP URLs that browsers block:
 *   <link href="http://prime7.wlkfkskakdf.com/style.css">
 *   <script src="http://prime7.wlkfkskakdf.com/main.js">
 * 
 * The solution: Replace ALL game server URLs to go through the proxy:
 *   <link href="https://moneyking77.online/api/buffalo/proxy-resource?url=http://prime7.wlkfkskakdf.com/style.css">
 * 
 * This proxies EVERYTHING (HTML, CSS, JS, images) through HTTPS!
 * 
 * REPLACE your entire proxyGame() method with this:
 */

/**
 * Proxy Game Content and Resources - Complete HTTPS Solution
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
        // Fetch the content from HTTP server
        $response = \Illuminate\Support\Facades\Http::timeout(30)
            ->withOptions(['verify' => false])
            ->get($gameUrl);
        
        if (!$response->successful()) {
            Log::error('Buffalo Proxy - Failed to fetch', [
                'url' => $gameUrl,
                'status' => $response->status()
            ]);
            
            return response()->json([
                'error' => 'Failed to fetch resource',
                'status' => $response->status()
            ], $response->status() ?: 500);
        }
        
        // Get content
        $content = $response->body();
        $contentType = $response->header('Content-Type') ?? 'application/octet-stream';
        
        // If it's HTML, rewrite all HTTP URLs to go through proxy
        if (strpos($contentType, 'text/html') !== false) {
            $gameServerUrl = 'http://prime7.wlkfkskakdf.com';
            $proxyBaseUrl = url('/api/buffalo/proxy-resource?url=');
            
            // Replace all absolute URLs pointing to game server
            // This covers: href="http://...", src="http://...", url('http://...'), etc.
            $content = str_replace(
                $gameServerUrl,
                $proxyBaseUrl . urlencode($gameServerUrl),
                $content
            );
            
            // Also handle protocol-relative URLs (//prime7.wlkfkskakdf.com)
            $content = str_replace(
                '//prime7.wlkfkskakdf.com',
                $proxyBaseUrl . urlencode('http://prime7.wlkfkskakdf.com'),
                $content
            );
            
            Log::info('Buffalo Proxy - Rewrote URLs in HTML', [
                'url' => $gameUrl,
                'content_length' => strlen($content)
            ]);
        }
        
        // For CSS files, also rewrite URLs
        if (strpos($contentType, 'text/css') !== false) {
            $gameServerUrl = 'http://prime7.wlkfkskakdf.com';
            $proxyBaseUrl = url('/api/buffalo/proxy-resource?url=');
            
            // Replace URLs in CSS (url('...'), url("..."), url(...))
            $content = preg_replace_callback(
                '/url\(["\']?(http:\/\/prime7\.wlkfkskakdf\.com[^"\')]*)["\']?\)/i',
                function($matches) use ($proxyBaseUrl) {
                    return 'url("' . $proxyBaseUrl . urlencode($matches[1]) . '")';
                },
                $content
            );
        }
        
        Log::info('Buffalo Proxy - Successfully proxied', [
            'url' => $gameUrl,
            'content_type' => $contentType,
            'content_length' => strlen($content)
        ]);
        
        // Return the content with headers that allow iframe embedding
        return response($content, 200)
            ->header('Content-Type', $contentType)
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Content-Security-Policy', 'frame-ancestors *')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', '*')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Cache-Control', 'public, max-age=3600'); // Cache for 1 hour
            
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

/**
 * Proxy game resources (CSS, JS, images, etc.)
 * This is called by the rewritten URLs in the HTML
 */
public function proxyResource(Request $request)
{
    $resourceUrl = $request->query('url');
    
    if (!$resourceUrl) {
        return response()->json(['error' => 'No URL provided'], 400);
    }
    
    // Validate it's the game server
    if (!str_starts_with($resourceUrl, 'http://prime7.wlkfkskakdf.com')) {
        return response()->json(['error' => 'Invalid URL'], 403);
    }
    
    try {
        // Use the main proxy method to handle the resource
        $request->merge(['url' => $resourceUrl]);
        return $this->proxyGame($request);
        
    } catch (\Exception $e) {
        Log::error('Buffalo Proxy Resource - Error', [
            'url' => $resourceUrl,
            'error' => $e->getMessage()
        ]);
        
        return response('', 404);
    }
}

/*
 * ==============================================================================
 * HOW THIS WORKS
 * ==============================================================================
 * 
 * 1. User requests game through proxy
 * 2. Proxy fetches HTML from HTTP game server
 * 3. Proxy rewrites ALL "http://prime7.wlkfkskakdf.com/..." URLs to:
 *    "https://moneyking77.online/api/buffalo/proxy-resource?url=http://..."
 * 4. Browser loads HTML (all URLs now point to HTTPS proxy)
 * 5. When browser requests CSS/JS/images, they go through proxy-resource
 * 6. Proxy-resource fetches from HTTP and returns through HTTPS
 * 7. Everything works! All content is HTTPS! ✅
 * 
 * Example transformation:
 * 
 * Original HTML from game server:
 *   <link href="http://prime7.wlkfkskakdf.com/style.css">
 *   <script src="http://prime7.wlkfkskakdf.com/main.js">
 * 
 * After proxy rewrite:
 *   <link href="https://moneyking77.online/api/buffalo/proxy-resource?url=http%3A%2F%2Fprime7.wlkfkskakdf.com%2Fstyle.css">
 *   <script src="https://moneyking77.online/api/buffalo/proxy-resource?url=http%3A%2F%2Fprime7.wlkfkskakdf.com%2Fmain.js">
 * 
 * ==============================================================================
 */

