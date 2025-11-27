<?php

namespace App\Http\Controllers\Api\V1\Game;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use App\Services\BuffaloGameService;
use App\Models\LogBuffaloBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BuffaloGameController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Buffalo Game - Get User Balance
     * Endpoint: POST /api/buffalo/get-user-balance
     */
    public function getUserBalance(Request $request)
    {
        Log::info('GameStar77 Buffalo getUserBalance - Request received', [
            'request' => $request->all(),
            'ip' => $request->ip()
        ]);

        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // Log received payload for debugging
        Log::info('GameStar77 Buffalo getUserBalance - Payload received', [
            'received_uid' => $uid,
            'received_token' => substr($token, 0, 20) . '...' . substr($token, -10), // Show first 20 and last 10 chars
            'token_length' => strlen($token),
            'ip' => $request->ip(),
        ]);

        // Extract username from UID first to generate expected values
        $userName = BuffaloGameService::extractUserNameFromUid($uid);
        
        if ($userName) {
            // Generate expected UID and token for comparison
            $expectedUid = BuffaloGameService::generateUid($userName);
            $expectedToken = BuffaloGameService::generatePersistentToken($userName);
            
            // Log expected values for debugging
            Log::info('GameStar77 Buffalo getUserBalance - Expected values', [
                'extracted_username' => $userName,
                'expected_uid' => $expectedUid,
                'expected_token' => substr($expectedToken, 0, 20) . '...' . substr($expectedToken, -10),
                'uid_match' => $uid === $expectedUid,
                'token_match' => hash_equals($expectedToken, $token),
            ]);
        } else {
            Log::warning('GameStar77 Buffalo getUserBalance - Could not extract username from UID', [
                'received_uid' => $uid,
            ]);
        }

        // Verify token
        Log::info('GameStar77 Buffalo - Token verification attempt', [
            'uid' => $uid,
            'token_preview' => substr($token, 0, 20) . '...'
        ]);
        
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            Log::warning('GameStar77 Buffalo - Token verification failed', [
                'uid' => $uid,
                'received_token' => substr($token, 0, 20) . '...' . substr($token, -10),
                'extracted_username' => $userName ?? 'N/A',
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('GameStar77 Buffalo - Token verification successful', [
            'uid' => $uid,
            'username' => $userName
        ]);

        if (!$userName) {
            Log::warning('GameStar77 Buffalo - Could not extract username', [
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid UID format',
            ]);
        }

        // Find user by username
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('GameStar77 Buffalo - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Get balance (assuming you use bavix/laravel-wallet)
        $balance = $user->balanceFloat;

        Log::info('GameStar77 Buffalo - Balance retrieved successfully', [
            'user' => $userName,
            'balance' => $balance
        ]);

        // Return balance as integer (Buffalo provider expects integer only)
        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'balance' => (int) $balance,
        ]);
    }

    /**
     * Buffalo Game - Change Balance (Bet/Win)
     * Endpoint: POST /api/buffalo/change-balance
     */
    public function changeBalance(Request $request)
    {
        Log::info('GameStar77 Buffalo changeBalance - Request received', [
            'request' => $request->all(),
            'ip' => $request->ip()
        ]);

        // Handle both form data and JSON (API docs specify form, but support both)
        $request->validate([
            'uid' => 'required|string|max:50',
            'bet_uid' => 'required|string', // Unique bet identifier for idempotency
            'token' => 'required|string',
            'changemoney' => 'required|integer',
            'bet' => 'required|integer',
            'win' => 'required|integer',
            'gameId' => 'nullable|integer', // Support both gameId and gameld
            'gameld' => 'nullable|integer', // API docs typo, but handle it
            'roomId' => 'nullable|integer', // Support both roomId and roomld
            'roomld' => 'nullable|integer', // API docs typo, but handle it
        ]);

        $uid = $request->uid;
        $token = $request->token;
        $betUid = $request->bet_uid;
        
        // Handle parameter name variations (gameld/gameId, roomld/roomId)
        $gameId = $request->gameId ?? $request->gameld ?? null;
        $roomId = $request->roomId ?? $request->roomld ?? null;

        // Log received payload for debugging
        Log::info('GameStar77 Buffalo changeBalance - Payload received', [
            'received_uid' => $uid,
            'received_token' => substr($token, 0, 20) . '...' . substr($token, -10), // Show first 20 and last 10 chars
            'token_length' => strlen($token),
            'bet_uid' => $betUid,
            'changemoney' => $request->changemoney,
            'bet' => $request->bet,
            'win' => $request->win,
            'game_id' => $gameId,
            'room_id' => $roomId,
            'ip' => $request->ip(),
        ]);

        // Extract username from UID first to generate expected values
        $userName = BuffaloGameService::extractUserNameFromUid($uid);
        
        if ($userName) {
            // Generate expected UID and token for comparison
            $expectedUid = BuffaloGameService::generateUid($userName);
            $expectedToken = BuffaloGameService::generatePersistentToken($userName);
            
            // Log expected values for debugging
            Log::info('GameStar77 Buffalo changeBalance - Expected values', [
                'extracted_username' => $userName,
                'expected_uid' => $expectedUid,
                'expected_token' => substr($expectedToken, 0, 20) . '...' . substr($expectedToken, -10),
                'uid_match' => $uid === $expectedUid,
                'token_match' => hash_equals($expectedToken, $token),
            ]);
        } else {
            Log::warning('GameStar77 Buffalo changeBalance - Could not extract username from UID', [
                'received_uid' => $uid,
            ]);
        }

        // Verify token
        Log::info('GameStar77 Buffalo - Token verification attempt', [
            'uid' => $uid,
            'token_preview' => substr($token, 0, 20) . '...',
            'bet_uid' => $betUid
        ]);
        
        if (!BuffaloGameService::verifyToken($uid, $token)) {
            Log::warning('GameStar77 Buffalo - Token verification failed', [
                'uid' => $uid,
                'received_token' => substr($token, 0, 20) . '...' . substr($token, -10),
                'extracted_username' => $userName ?? 'N/A',
                'bet_uid' => $betUid,
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('GameStar77 Buffalo - Token verification successful', [
            'uid' => $uid,
            'username' => $userName,
            'bet_uid' => $betUid
        ]);

        if (!$userName) {
            Log::warning('GameStar77 Buffalo - Could not extract username', [
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid UID format',
            ]);
        }

        // Find user
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('6TriBet Buffalo - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Idempotency Check: Prevent duplicate processing using bet_uid
        $existingBet = LogBuffaloBet::where('bet_uid', $betUid)->first();
        if ($existingBet) {
            Log::info('6TriBet Buffalo - Duplicate bet_uid detected, returning existing result', [
                'bet_uid' => $betUid,
                'user' => $user->user_name
            ]);
            
            // Return success with current balance in cents (as per API docs)
            $user->refresh();
            $balanceInCents = (int) ($user->balanceFloat * 100);
            
            return response()->json([
                'code' => 1,
                'msg' => (string) $balanceInCents, // API docs: msg contains "User balance in cents"
            ]);
        }

        // Get amounts
        $changeAmount = (int) $request->changemoney;
        $betAmount = abs((int) $request->bet);
        $winAmount = (int) $request->win;

        Log::info('6TriBet Buffalo - Processing transaction', [
            'user_name' => $user->user_name,
            'user_id' => $user->id,
            'bet_uid' => $betUid,
            'change_amount' => $changeAmount,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'game_id' => $gameId,
            'room_id' => $roomId
        ]);

        try {
            DB::beginTransaction();

            // Handle transaction
            if ($changeAmount > 0) {
                // Win/Deposit transaction
                $success = $this->walletService->deposit(
                    $user,
                    $changeAmount,
                    TransactionName::GameWin,
                    [
                        'buffalo_game_id' => $gameId,
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'bet_uid' => $betUid,
                        'room_id' => $roomId,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_win'
                    ]
                );
            } else {
                // Loss/Withdraw transaction
                $success = $this->walletService->withdraw(
                    $user,
                    abs($changeAmount),
                    TransactionName::GameLoss,
                    [
                        'buffalo_game_id' => $gameId,
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'bet_uid' => $betUid,
                        'room_id' => $roomId,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_loss'
                    ]
                );
            }

            if (!$success) {
                DB::rollBack();
                
                Log::error('6TriBet Buffalo - Wallet transaction failed', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'change_amount' => $changeAmount
                ]);
                
                return response()->json([
                    'code' => 0,
                    'msg' => 'Transaction failed',
                ]);
            }

            // Refresh user model
            $user->refresh();

            Log::info('6TriBet Buffalo - Transaction successful', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'bet_uid' => $betUid,
                'change_amount' => $changeAmount,
                'new_balance' => $user->balanceFloat
            ]);

            // Log the bet
            $this->logBuffaloBet($user, $request->all(), $betUid, $gameId, $roomId);

            DB::commit();

            // API docs specify: msg should contain "User balance in cents"
            $balanceInCents = (int) ($user->balanceFloat * 100);

            return response()->json([
                'code' => 1,
                'msg' => (string) $balanceInCents, // User balance in cents as per API docs
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('6TriBet Buffalo - Transaction error', [
                'user_name' => $user->user_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Transaction failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Log Buffalo bet for reporting
     */
    private function logBuffaloBet(User $user, array $requestData, string $betUid, ?int $gameId, ?int $roomId): void
    {
        try {
            LogBuffaloBet::create([
                'member_account' => $user->user_name,
                'bet_uid' => $betUid,
                'player_id' => $user->id,
                'player_agent_id' => $user->agent_id,
                'buffalo_game_id' => $gameId ?? $requestData['gameId'] ?? $requestData['gameld'] ?? null,
                'room_id' => $roomId ?? $requestData['roomId'] ?? $requestData['roomld'] ?? null,
                'request_time' => now(),
                'bet_amount' => abs((int) $requestData['bet']),
                'win_amount' => (int) $requestData['win'],
                'payload' => $requestData,
                'game_name' => 'Buffalo Game',
                'status' => 'completed',
                'before_balance' => $user->balanceFloat - ($requestData['changemoney'] ?? 0),
                'balance' => $user->balanceFloat,
            ]);

            Log::info('6TriBet Buffalo - Bet logged successfully', [
                'user' => $user->user_name,
                'bet_uid' => $betUid,
                'game_id' => $gameId
            ]);

        } catch (\Exception $e) {
            Log::error('6TriBet Buffalo - Failed to log bet', [
                'error' => $e->getMessage(),
                'user' => $user->user_name,
                'bet_uid' => $betUid
            ]);
        }
    }

    /**
     * Generate Buffalo game authentication data for frontend
     */
    public function generateGameAuth(Request $request)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $auth = BuffaloGameService::generateBuffaloAuth($user);
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        $roomConfig = BuffaloGameService::getRoomConfig();

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'auth' => $auth,
                'available_rooms' => $availableRooms,
                'all_rooms' => $roomConfig,
                'user_balance' => $user->balanceFloat,
            ],
        ]);
    }

    /**
     * Generate Buffalo game URL for direct launch
     */
    public function generateGameUrl(Request $request)
    {
        $request->validate([
            'room_id' => 'required|integer|min:1|max:4',
            'lobby_url' => 'nullable|url',
            'game_id' => 'nullable|integer|in:23,42', // 23 = normal buffalo, 42 = scatter buffalo
        ]);

        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $roomId = $request->room_id;
        $lobbyUrl = $request->lobby_url ?: config('app.url');
        $gameId = $request->game_id ?? 23; // Default to normal buffalo (23)

        // Check if user has sufficient balance for the room
        $availableRooms = BuffaloGameService::getAvailableRooms($user);
        
        if (!isset($availableRooms[$roomId])) {
            return response()->json([
                'code' => 0,
                'msg' => 'Insufficient balance for selected room',
            ]);
        }

        try {
            // Call provider's Game Login API to get game URL
            $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl, $gameId);

            return response()->json([
                'code' => 1,
                'msg' => 'Success',
                'data' => [
                    'game_url' => $gameUrl,
                    'room_info' => $availableRooms[$roomId],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Buffalo generateGameUrl - API Error', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'room_id' => $roomId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Failed to generate game URL: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Buffalo Game - Launch Game (Frontend Integration)
     * Compatible with existing frontend LaunchGame hook
     */
    public function launchGame(Request $request)
    {
        $request->validate([
            'type_id' => 'required|integer',
            'provider_id' => 'required|integer',
            'game_id' => 'required|integer',
            'room_id' => 'nullable|integer|min:1|max:4', // Optional room selection
        ]);

        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ], 401);
        }

        try {
            // Check if this is a Buffalo game request
            if ($request->provider_id === 23) { // Assuming 23 is Buffalo provider ID
                // Generate Buffalo game authentication
                $auth = BuffaloGameService::generateBuffaloAuth($user);
                
                // Log generated auth data for debugging
                Log::info('6TriBet Buffalo Game Launch - Generated auth data', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'generated_uid' => $auth['uid'],
                    'generated_token' => substr($auth['token'], 0, 20) . '...' . substr($auth['token'], -10),
                    'token_length' => strlen($auth['token']),
                ]);
                
                // Get room configuration
                $roomId = $request->room_id ?? 1; // Default to room 1
                $availableRooms = BuffaloGameService::getAvailableRooms($user);
                
                // Check if requested room is available for user's balance
                if (!isset($availableRooms[$roomId])) {
                    return response()->json([
                        'code' => 0,
                        'msg' => 'Room not available for your balance level',
                    ]);
                }
                
                $roomConfig = $availableRooms[$roomId];
                
                // Determine game type: use game_id from request or default to normal buffalo (23)
                // game_id 23 = normal buffalo, 42 = scatter buffalo
                $gameId = $request->game_id ?? 23;
                
                // Log request payload for debugging
                Log::info('6TriBet Buffalo Game Launch - Request payload', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'type_id' => $request->type_id,
                    'provider_id' => $request->provider_id,
                    'game_id' => $gameId,
                    'room_id' => $roomId,
                    'user_balance' => $user->balanceFloat,
                ]);
                
                // Generate Buffalo game URL by calling provider's Game Login API
                $lobbyUrl = config('buffalo.site.url', 'https://maxwinmyanmar.pro');
                $gameUrl = BuffaloGameService::generateGameUrl($user, $roomId, $lobbyUrl, $gameId);
                
                // Note: The game URL is now returned directly from the provider API
                // No need to manually add UID and token as the API handles authentication
                
                Log::info('6TriBet Buffalo Game Launch - Success', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'room_id' => $roomId,
                    'game_id' => $gameId,
                    'game_url' => $gameUrl,
                    'generated_uid' => $auth['uid'],
                    'generated_token_preview' => substr($auth['token'], 0, 20) . '...' . substr($auth['token'], -10),
                ]);
                
                return response()->json([
                    'code' => 1,
                    'msg' => 'Game launched successfully',
                    'Url' => $gameUrl, // Compatible with existing frontend
                    'game_url' => $gameUrl, // Game URL from provider API
                    'room_info' => $roomConfig,
                    'user_balance' => $user->balanceFloat,
                ]);
            }
            
            // For non-Buffalo games, you can add other provider logic here
            return response()->json([
                'code' => 0,
                'msg' => 'Game provider not supported',
            ]);
            
        } catch (\Exception $e) {
            Log::error('6TriBet Buffalo Game Launch Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Failed to launch game: ' . $e->getMessage(),
            ]);
        }
    }

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
    // Support both old and new game server URLs
    $allowedDomains = [
        'http://prime7.wlkfkskakdf.com',
        'https://prime.next-api.net',
        'http://prime.next-api.net',
    ];
    
    $isValidUrl = false;
    foreach ($allowedDomains as $domain) {
        if (str_starts_with($gameUrl, $domain)) {
            $isValidUrl = true;
            break;
        }
    }
    
    if (!$isValidUrl) {
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
        
        // If it's HTML, rewrite all URLs to go through proxy
        if (strpos($contentType, 'text/html') !== false) {
            // Detect game server URL from the game URL
            $gameServerUrl = 'http://prime7.wlkfkskakdf.com'; // Default
            if (str_contains($gameUrl, 'prime.next-api.net')) {
                $gameServerUrl = 'https://prime.next-api.net';
            }
            $proxyBaseUrl = url('/api/buffalo/proxy-resource?url=');
            
            // First, handle all root-relative paths (most important for game assets)
            // Match src="/file.js", href="/style.css", etc.
            $content = preg_replace_callback(
                '/(src|href|data-src|data-href)=(["\'])\/([^"\']*)\2/i',
                function($matches) use ($proxyBaseUrl, $gameServerUrl) {
                    $attr = $matches[1];
                    $quote = $matches[2];
                    $path = $matches[3];
                    $fullUrl = $gameServerUrl . '/' . $path;
                    return $attr . '=' . $quote . $proxyBaseUrl . urlencode($fullUrl) . $quote;
                },
                $content
            );
            
            // Handle paths in url() for CSS (in style attributes or inline styles)
            $content = preg_replace_callback(
                '/url\(["\']?\/([^"\')]+)["\']?\)/i',
                function($matches) use ($proxyBaseUrl, $gameServerUrl) {
                    $path = $matches[1];
                    $fullUrl = $gameServerUrl . '/' . $path;
                    return 'url("' . $proxyBaseUrl . urlencode($fullUrl) . '")';
                },
                $content
            );
            
            // Replace all absolute URLs pointing to game server
            $content = str_replace(
                [$gameServerUrl, '//prime7.wlkfkskakdf.com'],
                [$proxyBaseUrl . urlencode($gameServerUrl), $proxyBaseUrl . urlencode('http://prime7.wlkfkskakdf.com')],
                $content
            );
            
            // Add a base tag as fallback (though the above rewrites should catch everything)
            $baseTag = "\n" . '<base href="' . $proxyBaseUrl . urlencode($gameServerUrl . '/') . '">' . "\n";
            if (preg_match('/<head[^>]*>/i', $content)) {
                $content = preg_replace('/<head[^>]*>/i', '$0' . $baseTag, $content, 1);
            } else {
                $content = $baseTag . $content;
            }
            
            Log::info('Buffalo Proxy - Rewrote URLs in HTML', [
                'url' => $gameUrl,
                'content_length' => strlen($content),
                'rewrites' => [
                    'root_relative' => substr_count($content, $proxyBaseUrl),
                ]
            ]);
        }
        
        // For CSS files, also rewrite URLs
        if (strpos($contentType, 'text/css') !== false) {
            // Detect game server URL from the game URL
            $gameServerUrl = 'http://prime7.wlkfkskakdf.com'; // Default
            if (str_contains($gameUrl, 'prime.next-api.net')) {
                $gameServerUrl = 'https://prime.next-api.net';
            }
            $proxyBaseUrl = url('/api/buffalo/proxy-resource?url=');
            
            // Replace URLs in CSS (support both old and new domains)
            $content = preg_replace_callback(
                '/url\(["\']?(https?:\/\/(?:prime7\.wlkfkskakdf\.com|prime\.next-api\.net)[^"\')]*)["\']?\)/i',
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
    
    // Validate it's the game server (support both old and new URLs)
    $allowedDomains = [
        'http://prime7.wlkfkskakdf.com',
        'https://prime.next-api.net',
        'http://prime.next-api.net',
    ];
    
    $isValidUrl = false;
    foreach ($allowedDomains as $domain) {
        if (str_starts_with($resourceUrl, $domain)) {
            $isValidUrl = true;
            break;
        }
    }
    
    if (!$isValidUrl) {
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


}