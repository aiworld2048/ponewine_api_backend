<?php

namespace App\Http\Controllers\Api\V1\Game\buffalo_multi;

use App\Enums\TransactionName;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use App\Services\BuffaloGameMultiSiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class BuffaloGameMultiSiteController extends Controller
{
    protected WalletService $WalletService;

    public function __construct(WalletService $WalletService)
    {
        $this->WalletService = $WalletService;
    }

    /**
     * Buffalo Game - Get User Balance (Multi-Site Support)
     */
    public function getUserBalance(Request $request)
    {
        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // Extract site prefix
        $prefix = BuffaloGameMultiSiteService::extractPrefix($uid);
        $siteConfig = BuffaloGameMultiSiteService::getSiteConfig($prefix);

        Log::info('Buffalo getUserBalance - Request received', [
            'uid' => $uid,
            'prefix' => $prefix,
            'site' => $siteConfig['name'] ?? 'Unknown'
        ]);

        // Check if site exists and is enabled
        if (!$siteConfig || !$siteConfig['enabled']) {
            Log::warning('Buffalo getUserBalance - Invalid or disabled site', [
                'prefix' => $prefix,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid site',
            ]);
        }

        // Route based on site configuration
        if ($siteConfig['is_local']) {
            // Handle locally
            return $this->handleLocalGetBalance($request, $uid, $token, $prefix);
        } else {
            // Forward to external API
            return $this->forwardGetBalanceToExternalSite($request, $prefix);
        }
    }

    /**
     * Handle get balance for local site
     */
    private function handleLocalGetBalance(Request $request, string $uid, string $token, string $prefix)
    {
        // Verify token
        Log::info('Buffalo getUserBalance - Token verification attempt (Local)', [
            'uid' => $uid,
            'prefix' => $prefix
        ]);
        
        if (!BuffaloGameMultiSiteService::verifyToken($uid, $token)) {
            Log::warning('Buffalo getUserBalance - Token verification failed', [
                'uid' => $uid,
                'token' => $token
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('Buffalo getUserBalance - Token verification successful', [
            'uid' => $uid
        ]);

        // Extract user_name from uid
        $userName = BuffaloGameMultiSiteService::extractUserNameFromUid($uid, $prefix);

        // Lookup user by user_name
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('Buffalo getUserBalance - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // Get balance using bavix wallet trait
        $balance = $user->balanceFloat;

        Log::info('Buffalo getUserBalance - Success', [
            'user' => $userName,
            'balance' => $balance
        ]);

        // Return balance as integer (provider expects integer only)
        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'balance' => (int) $balance,
        ]);
    }

    /**
     * Forward get balance request to external site
     */
    private function forwardGetBalanceToExternalSite(Request $request, string $prefix)
    {
        $externalApiUrl = BuffaloGameMultiSiteService::getExternalApiUrl($prefix, 'get_balance');
        
        if (!$externalApiUrl) {
            Log::error('Buffalo getUserBalance - External API URL not configured', [
                'prefix' => $prefix
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Site configuration error',
            ]);
        }

        Log::info('Buffalo getUserBalance - Forwarding to external site', [
            'prefix' => $prefix,
            'external_url' => $externalApiUrl
        ]);

        try {
            $response = Http::timeout(10)->post($externalApiUrl, $request->all());
            
            if ($response->successful()) {
                Log::info('Buffalo getUserBalance - External API success', [
                    'prefix' => $prefix,
                    'response' => $response->json()
                ]);
                
                return response()->json($response->json(), 200);
            } else {
                Log::error('Buffalo getUserBalance - External API failed', [
                    'prefix' => $prefix,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return response()->json([
                    'code' => 0,
                    'msg' => 'External API error',
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Buffalo getUserBalance - External API exception', [
                'prefix' => $prefix,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Connection error',
            ], 500);
        }
    }

    /**
     * Buffalo Game - Change Balance (Bet/Win) - Multi-Site Support
     */
    public function changeBalance(Request $request)
    {
        Log::info('Buffalo changeBalance - Request received', [
            'request' => $request->all()
        ]);
        
        $request->validate([
            'uid' => 'required|string|max:50',
            'token' => 'required|string',
            'changemoney' => 'required|integer',
            'bet' => 'required|integer',
            'win' => 'required|integer',
            'gameId' => 'required|integer',
        ]);

        $uid = $request->uid;
        $token = $request->token;

        // Extract site prefix
        $prefix = BuffaloGameMultiSiteService::extractPrefix($uid);
        $siteConfig = BuffaloGameMultiSiteService::getSiteConfig($prefix);

        Log::info('Buffalo changeBalance - Site detected', [
            'uid' => $uid,
            'prefix' => $prefix,
            'site' => $siteConfig['name'] ?? 'Unknown'
        ]);

        // Check if site exists and is enabled
        if (!$siteConfig || !$siteConfig['enabled']) {
            Log::warning('Buffalo changeBalance - Invalid or disabled site', [
                'prefix' => $prefix,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid site',
            ]);
        }

        // Route based on site configuration
        if ($siteConfig['is_local']) {
            // Handle locally
            return $this->handleLocalChangeBalance($request, $uid, $token, $prefix);
        } else {
            // Forward to external API
            return $this->forwardChangeBalanceToExternalSite($request, $prefix);
        }
    }

    /**
     * Handle change balance for local site
     */
    private function handleLocalChangeBalance(Request $request, string $uid, string $token, string $prefix)
    {
        // Verify token
        Log::info('Buffalo changeBalance - Token verification attempt (Local)', [
            'uid' => $uid,
            'prefix' => $prefix
        ]);
        
        if (!BuffaloGameMultiSiteService::verifyToken($uid, $token)) {
            Log::warning('Buffalo changeBalance - Token verification failed', [
                'uid' => $uid,
                'token' => $token
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid token',
            ]);
        }
        
        Log::info('Buffalo changeBalance - Token verification successful', [
            'uid' => $uid
        ]);

        // Extract user_name from uid
        $userName = BuffaloGameMultiSiteService::extractUserNameFromUid($uid, $prefix);

        // Lookup user by user_name
        $user = User::where('user_name', $userName)->first();
        
        if (!$user) {
            Log::warning('Buffalo changeBalance - User not found', [
                'userName' => $userName,
                'uid' => $uid
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'User not found',
            ]);
        }

        // ✅ Use amounts directly (provider expects integer values)
        $changeAmount = (int) $request->changemoney; // Convert to integer
        $betAmount = abs((int) $request->bet);       // Convert to integer
        $winAmount = (int) $request->win;            // Convert to integer

        Log::info('Buffalo Game Transaction', [
            'user_name' => $user->user_name,
            'user_id' => $user->id,
            'change_amount' => $changeAmount,
            'bet_amount' => $betAmount,
            'win_amount' => $winAmount,
            'game_id' => $request->gameId,
            'original_request' => $request->all()
        ]);

        try {
            // ✅ Handle different transaction types
            if ($changeAmount > 0) {
                // Win/Deposit transaction
                $success = $this->WalletService->deposit(
                    $user,
                    $changeAmount,
                    TransactionName::GameWin,
                    [
                        'buffalo_game_id' => $request->gameId, // Provider confirmed: integer
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_win'
                    ]
                );
            } else {
                // Loss/Withdraw transaction
                $success = $this->WalletService->withdraw(
                    $user,
                    abs($changeAmount),
                    TransactionName::GameLoss,
                    [
                        'buffalo_game_id' => $request->gameId, // Provider confirmed: integer
                        'bet_amount' => $betAmount,
                        'win_amount' => $winAmount,
                        'provider' => 'buffalo',
                        'transaction_type' => 'game_loss'
                    ]
                );
            }

            if (!$success) {
                Log::error('Buffalo Game - Wallet transaction failed', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'change_amount' => $changeAmount,
                    'bet_amount' => $betAmount,
                    'win_amount' => $winAmount,
                    'game_id' => $request->gameId,
                    'transaction_type' => $changeAmount > 0 ? 'deposit' : 'withdraw'
                ]);
                
                return response()->json([
                    'code' => 0,
                    'msg' => 'Transaction failed',
                ]);
            }

            // ✅ Refresh user model to get updated balance
            $user->refresh();

            Log::info('Buffalo Game - Wallet transaction successful', [
                'user_id' => $user->id,
                'user_name' => $user->user_name,
                'change_amount' => $changeAmount,
                'new_balance' => $user->balanceFloat,
                'transaction_type' => $changeAmount > 0 ? 'deposit' : 'withdraw'
            ]);

            // ✅ Log the bet data for reporting
            $this->logBuffaloBet($user, $request->all());

            return response()->json([
                'code' => 1,
                'msg' => 'Balance updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Buffalo Game Transaction Error', [
                'user_name' => $user->user_name,
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Transaction failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Forward change balance request to external site
     */
    private function forwardChangeBalanceToExternalSite(Request $request, string $prefix)
    {
        $externalApiUrl = BuffaloGameMultiSiteService::getExternalApiUrl($prefix, 'change_balance');
        
        if (!$externalApiUrl) {
            Log::error('Buffalo changeBalance - External API URL not configured', [
                'prefix' => $prefix
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Site configuration error',
            ]);
        }

        Log::info('Buffalo changeBalance - Forwarding to external site', [
            'prefix' => $prefix,
            'external_url' => $externalApiUrl,
            'request_data' => $request->all()
        ]);

        try {
            $response = Http::timeout(10)->post($externalApiUrl, $request->all());
            
            if ($response->successful()) {
                Log::info('Buffalo changeBalance - External API success', [
                    'prefix' => $prefix,
                    'response' => $response->json()
                ]);
                
                return response()->json($response->json(), 200);
            } else {
                Log::error('Buffalo changeBalance - External API failed', [
                    'prefix' => $prefix,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return response()->json([
                    'code' => 0,
                    'msg' => 'External API error',
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Buffalo changeBalance - External API exception', [
                'prefix' => $prefix,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Connection error',
            ], 500);
        }
    }

    /**
     * Forward launch game request to external site
     */
    private function forwardLaunchGameToExternalSite(Request $request, string $prefix)
    {
        $externalApiUrl = BuffaloGameMultiSiteService::getExternalApiUrl($prefix, 'launch_game');

        if (!$externalApiUrl) {
            Log::error('Buffalo launchGame - External API URL not configured', [
                'prefix' => $prefix,
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Site configuration error',
            ]);
        }

        Log::info('Buffalo launchGame - Forwarding to external site', [
            'prefix' => $prefix,
            'external_url' => $externalApiUrl,
            'request_data' => $request->all(),
        ]);

        try {
            $payload = array_merge($request->all(), ['site_prefix' => $prefix]);
            $response = Http::timeout(10)->post($externalApiUrl, $payload);

            if ($response->successful()) {
                Log::info('Buffalo launchGame - External API success', [
                    'prefix' => $prefix,
                    'response' => $response->json(),
                ]);

                return response()->json($response->json(), 200);
            }

            Log::error('Buffalo launchGame - External API failed', [
                'prefix' => $prefix,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'External API error',
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Buffalo launchGame - External API exception', [
                'prefix' => $prefix,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'code' => 0,
                'msg' => 'Connection error',
            ], 500);
        }
    }

    /**
     * Generate Buffalo game authentication data for frontend
     */
    public function generateGameAuth(Request $request)
    {
        $request->validate([
            'site_prefix' => 'nullable|string|max:10',
        ]);

        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $sitePrefix = $this->resolveSitePrefix($request->input('site_prefix'));

        $auth = BuffaloGameMultiSiteService::generateBuffaloAuth($user, $sitePrefix);
        $availableRooms = BuffaloGameMultiSiteService::getAvailableRooms($user, $sitePrefix);
        $roomConfig = BuffaloGameMultiSiteService::getRoomConfig();

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'auth' => $auth,
                'available_rooms' => $availableRooms,
                'all_rooms' => $roomConfig,
                'user_balance' => $user->balanceFloat,
                'site_prefix' => $sitePrefix,
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
            'site_prefix' => 'nullable|string|max:10',
        ]);

        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'code' => 0,
                'msg' => 'User not authenticated',
            ]);
        }

        $sitePrefix = $this->resolveSitePrefix($request->input('site_prefix'));
        $roomId = $request->room_id;
        $lobbyUrl = $request->lobby_url ?: config('app.url');
        $siteConfig = BuffaloGameMultiSiteService::getSiteConfig($sitePrefix);

        if (!$siteConfig) {
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid site prefix',
            ], 422);
        }

        if (!$request->lobby_url && isset($siteConfig['lobby_url'])) {
            $lobbyUrl = $siteConfig['lobby_url'];
        }

        // Check if user has sufficient balance for the room
        $availableRooms = BuffaloGameMultiSiteService::getAvailableRooms($user, $sitePrefix);
        
        if (!isset($availableRooms[$roomId])) {
            return response()->json([
                'code' => 0,
                'msg' => 'Insufficient balance for selected room',
            ]);
        }

        $gameUrl = BuffaloGameMultiSiteService::generateGameUrl($user, $roomId, $sitePrefix, $lobbyUrl);

        return response()->json([
            'code' => 1,
            'msg' => 'Success',
            'data' => [
                'game_url' => $gameUrl,
                'room_info' => $availableRooms[$roomId],
                'site_prefix' => $sitePrefix,
            ],
        ]);
    }

    /**
     * Log Buffalo bet for reporting
     */
    private function logBuffaloBet(User $user, array $requestData): void
    {
        try {
            // ✅ Use LogBuffaloBet model with correct fields
            \App\Models\LogBuffaloBet::create([
                'member_account' => $user->user_name,
                'player_id' => $user->id,
                'player_agent_id' => $user->agent_id,
                'buffalo_game_id' => $requestData['gameId'], // Provider confirmed: integer
                'request_time' => now(),
                'bet_amount' => abs((int) $requestData['bet']), // Convert to integer
                'win_amount' => (int) $requestData['win'],      // Convert to integer
                'payload' => $requestData, // Store full request data
                'game_name' => 'Buffalo Slot Game',
                'status' => 'completed',
                'before_balance' => $user->balanceFloat - $requestData['changemoney'],
                'balance' => $user->balanceFloat,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log Buffalo bet', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $requestData
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
            'site_prefix' => 'nullable|string|max:10',
            'uid' => 'nullable|string|max:50',
            'token' => 'nullable|string',
        ]);

        $user = $request->user();
        $sitePrefix = null;
        $providedUid = $request->input('uid');
        $providedToken = $request->input('token');

        if ($user) {
            $sitePrefix = $this->resolveSitePrefix($request->input('site_prefix'));
        } else {
            $request->validate([
                'uid' => 'required|string|max:50',
                'token' => 'required|string',
            ]);

            $sitePrefix = BuffaloGameMultiSiteService::extractPrefix($providedUid);
        }

        $siteConfig = BuffaloGameMultiSiteService::getSiteConfig($sitePrefix);

        if (!$siteConfig || !($siteConfig['enabled'] ?? false)) {
            return response()->json([
                'code' => 0,
                'msg' => 'Invalid site prefix',
            ], 422);
        }

        $siteLobbyUrl = $siteConfig['lobby_url'] ?? null;
        $effectiveLobbyUrl = $siteLobbyUrl ?: config('app.url');
        $isLocalSite = $siteConfig['is_local'] ?? false;

        if (!$isLocalSite) {
            $request->validate([
                'uid' => 'required|string|max:50',
                'token' => 'required|string',
            ]);

            $roomId = $request->room_id ?? 1;

            try {
                $gameUrl = BuffaloGameMultiSiteService::getGameUrlWithCredentials(
                    $providedUid,
                    $providedToken,
                    $sitePrefix,
                    $roomId,
                    $effectiveLobbyUrl,
                    $request->game_id
                );

                return response()->json([
                    'code' => 1,
                    'msg' => 'Game launched successfully',
                    'Url' => $gameUrl,
                    'game_url' => $gameUrl,
                    'room_id' => $roomId,
                    'site_prefix' => $sitePrefix,
                    'lobby_url' => $siteLobbyUrl ?? '',
                ]);

            } catch (\Exception $e) {
                Log::error('Buffalo Game Launch External Error', [
                    'site_prefix' => $sitePrefix,
                    'error' => $e->getMessage(),
                    'request_data' => $request->all(),
                ]);

                return response()->json([
                    'code' => 0,
                    'msg' => 'Failed to launch game: ' . $e->getMessage(),
                ]);
            }
        }

        if (!$user) {
            if (!BuffaloGameMultiSiteService::verifyToken($providedUid, $providedToken)) {
                return response()->json([
                    'code' => 0,
                    'msg' => 'Invalid token',
                ], 401);
            }

            $userName = BuffaloGameMultiSiteService::extractUserNameFromUid($providedUid, $sitePrefix);

            if (!$userName) {
                return response()->json([
                    'code' => 0,
                    'msg' => 'User not found for UID',
                ], 404);
            }

            $user = User::where('user_name', $userName)->first();

            if (!$user) {
                return response()->json([
                    'code' => 0,
                    'msg' => 'User not found',
                ], 404);
            }
        }

        try {
            // Check if this is a Buffalo game request
            if ($request->provider_id === 23) { // Assuming 23 is Buffalo provider ID
                // Generate Buffalo game authentication
                $auth = BuffaloGameMultiSiteService::generateBuffaloAuth($user, $sitePrefix);
                
                // Get room configuration
                $roomId = $request->room_id ?? 1; // Default to room 1
                $availableRooms = BuffaloGameMultiSiteService::getAvailableRooms($user, $sitePrefix);
                
                // Check if requested room is available for user's balance
                if (!isset($availableRooms[$roomId])) {
                    return response()->json([
                        'code' => 0,
                        'msg' => 'Room not available for your balance level',
                    ]);
                }
                
                $roomConfig = $availableRooms[$roomId];
                
                // Generate Buffalo game URL (Production - HTTP as per provider format)
                $lobbyUrl = $effectiveLobbyUrl;
                $gameUrl = BuffaloGameMultiSiteService::generateGameUrl($user, $roomId, $sitePrefix, $lobbyUrl);
                
                // Add UID and token to the URL (exact provider format)
                $gameUrl .= '&uid=' . $auth['uid'] . '&token=' . $auth['token'];
                
                Log::info('Buffalo Game Launch', [
                    'user_id' => $user->id,
                    'user_name' => $user->user_name,
                    'room_id' => $roomId,
                    'game_url' => $gameUrl,
                    'auth_data' => $auth,
                    'site_prefix' => $sitePrefix,
                ]);
                
                return response()->json([
                    'code' => 1,
                    'msg' => 'Game launched successfully',
                    'Url' => $gameUrl, // Compatible with existing frontend
                    'game_url' => $gameUrl, // HTTP URL (exact provider format)
                    'room_info' => $roomConfig,
                    'user_balance' => $user->balanceFloat,
                    'site_prefix' => $sitePrefix,
                    'lobby_url' => $siteLobbyUrl ?? '',
                ]);
            }
            
            // For non-Buffalo games, you can add other provider logic here
            return response()->json([
                'code' => 0,
                'msg' => 'Game provider not supported',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Buffalo Game Launch Error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'code' => 0,
                'msg' => 'Failed to launch game',
            ]);
        }
    }

    /**
     * Determine which site prefix to use for frontend helpers
     */
    private function resolveSitePrefix(?string $sitePrefix): string
    {
        return $sitePrefix ?: config('buffalo_sites.default_site', 'mwm');
    }
}