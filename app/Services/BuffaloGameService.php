<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class BuffaloGameService
{
    /**
     * Site configuration for TriBet
     * Note: These are now loaded from config/buffalo.php
     * Kept as constants for backward compatibility
     */
    private const SITE_NAME = 'https://maxwinmyanmar.pro';
    private const SITE_PREFIX = 'mxm'; // az9
    private const SITE_URL = 'https://maxwinmyanmar.pro';

    /**
     * Generate UID (32 characters) for Buffalo API
     * Format: prefix(3) + base64_encoded_username(variable) + padding to 32 chars
     */
    public static function generateUid(string $userName): string
    {
        // Encode username to base64 (URL-safe)
        $encoded = rtrim(strtr(base64_encode($userName), '+/', '-_'), '=');
        
        // Create a 32-character UID: prefix + encoded username + hash padding
        $prefix = self::SITE_PREFIX; // 3 chars: "6t"
        $remaining = 32 - strlen($prefix);
        
        // If encoded username is longer than available space, use hash instead
        if (strlen($encoded) > $remaining - 10) {
            $hash = md5($userName . self::SITE_URL);
            return $prefix . substr($hash, 0, $remaining);
        }
        
        // Pad with hash to reach 32 characters total
        $padding = substr(md5($userName . self::SITE_URL), 0, $remaining - strlen($encoded));
        return $prefix . $encoded . $padding;
    }

    /**
     * Generate token (64 characters) for Buffalo API
     * Note: Buffalo provider doesn't use secret keys
     */
    public static function generateToken(string $uid): string
    {
        // Generate a 64-character token using SHA256
        return hash('sha256', $uid . self::SITE_URL . time());
    }

    /**
     * Generate persistent token for user (stored in database)
     */
    public static function generatePersistentToken(string $userName): string
    {
        // Generate persistent token using SHA256
        $uniqueString = $userName . self::SITE_URL . 'buffalo-persistent-token';
        return hash('sha256', $uniqueString);
    }

    /**
     * Verify token
     */
    public static function verifyToken(string $uid, string $token): bool
    {
        try {
            // Extract username from UID
            $userName = self::extractUserNameFromUid($uid);
            
            if (!$userName) {
                Log::warning('TriBet Buffalo - Could not extract username from UID', [
                    'uid' => $uid
                ]);
                return false;
            }

            // Find user
            $user = User::where('user_name', $userName)->first();
            
            if (!$user) {
                Log::warning('TriBet Buffalo - User not found for token verification', [
                    'userName' => $userName
                ]);
                return false;
            }

            // Generate expected token
            $expectedToken = self::generatePersistentToken($userName);

            $isValid = hash_equals($expectedToken, $token);

            if ($isValid) {
                Log::info('TriBet Buffalo - Token verified successfully', [
                    'user' => $userName
                ]);
            } else {
                Log::warning('TriBet Buffalo - Token verification failed', [
                    'user' => $userName,
                    'expected' => substr($expectedToken, 0, 10) . '...',
                    'received' => substr($token, 0, 10) . '...'
                ]);
            }

            return $isValid;

        } catch (\Exception $e) {
            Log::error('TriBet Buffalo - Token verification error', [
                'error' => $e->getMessage(),
                'uid' => $uid
            ]);
            return false;
        }
    }

    /**
     * Extract username from UID
     */
    public static function extractUserNameFromUid(string $uid): ?string
    {
        // Remove prefix (first 3 characters: "6t")
        $uidWithoutPrefix = substr($uid, 3);
        
        // Try to decode the base64 encoded part
        try {
            // Find the encoded username part (before the hash padding)
            for ($len = strlen($uidWithoutPrefix); $len >= 4; $len--) {
                $encodedPart = substr($uidWithoutPrefix, 0, $len);
                
                // Add back padding if needed
                $paddedEncoded = $encodedPart . str_repeat('=', (4 - strlen($encodedPart) % 4) % 4);
                
                // Try to decode
                $decoded = base64_decode(strtr($paddedEncoded, '-_', '+/'), true);
                
                if ($decoded !== false) {
                    // Clean the decoded string - remove any non-printable characters
                    $cleaned = preg_replace('/[^\x20-\x7E]/', '', $decoded);
                    
                    if (!empty($cleaned)) {
                        // Check if this username exists (use cleaned string)
                        $user = User::where('user_name', $cleaned)->first();
                        if ($user) {
                            return $cleaned;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('TriBet Buffalo - Failed to decode UID', [
                'uid' => $uid,
                'error' => $e->getMessage()
            ]);
        }

        // Fallback: Search by UID pattern in database
        try {
            $users = User::select('id', 'user_name')->get();
            foreach ($users as $user) {
                $generatedUid = self::generateUid($user->user_name);
                if ($generatedUid === $uid) {
                    return $user->user_name;
                }
            }
        } catch (\Exception $e) {
            Log::error('TriBet Buffalo - Error in fallback UID search', [
                'uid' => $uid,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Get game URL for user
     */
    public static function getGameUrl(User $user, int $roomId = 2): string
    {
        $uid = self::generateUid($user->user_name);
        $token = self::generatePersistentToken($user->user_name);

        $data = [
            "gameId" => 23, // Buffalo game ID
            "roomId" => $roomId,
            "uid" => $uid,
            "token" => $token,
            "lobbyUrl" => self::SITE_URL,
        ];

        $baseUrl = 'https://prime.next-api.net/';
        return $baseUrl . '?' . http_build_query($data);
    }

    /**
     * Generate Buffalo authentication data
     * Returns UID and Token for frontend
     */
    // public static function generateBuffaloAuth(User $user): array
    // {
    //     $uid = self::generateUid($user->user_name);
    //     $token = self::generatePersistentToken($user->user_name);

    //     return [
    //         'uid' => $uid,
    //         'token' => $token,
    //         'user_name' => $user->user_name,
    //     ];
    // }

    public static function generateBuffaloAuth(User $user): array
    {
        $uid = self::generateUid($user->user_name);
        $token = self::generatePersistentToken($user->user_name); // Pass username string, not User object

        return [
            'uid' => $uid,
            'token' => $token,
            'user_name' => $user->user_name,
        ];
    }

    /**
     * Generate Buffalo game URL with lobby URL
     */
    // public static function generateGameUrl(User $user, int $roomId, string $lobbyUrl): string
    // {
    //     $uid = self::generateUid($user->user_name);
    //     $token = self::generatePersistentToken($user->user_name);

    //     $data = [
    //         "gameId" => 23, // Buffalo game ID
    //         "roomId" => $roomId,
    //         "uid" => $uid,
    //         "token" => $token,
    //         "lobbyUrl" => $lobbyUrl,
    //     ];

    //     $baseUrl = 'http://prime7.wlkfkskakdf.com/';
    //     return $baseUrl . '?' . http_build_query($data);
    // }

    /**
     * Call provider's Game Login API to get game URL
     * 
     * @param User $user
     * @param int $roomId Room ID (1-4)
     * @param string $lobbyUrl Lobby redirect URL
     * @param int|null $gameId Game ID (23 for normal buffalo, 42 for scatter buffalo). Default: 23
     * @return string Game URL from provider
     * @throws \Exception If API call fails
     */
    public static function getGameUrlFromProvider(User $user, int $roomId = 1, string $lobbyUrl = '', ?int $gameId = null): string
    {
        $uid = self::generateUid($user->user_name);
        $token = self::generatePersistentToken($user->user_name);
        
        // Get configuration
        $apiUrl = Config::get('buffalo.api.url', 'https://api-ms3.african-buffalo.club/api/game-login');
        $domain = Config::get('buffalo.domain', 'prime.com');
        $timeout = Config::get('buffalo.timeout', 30);
        $gameServerUrl = Config::get('buffalo.game_server_url', 'https://prime.next-api.net');
        
        // Use provided gameId or default to normal buffalo (23)
        if ($gameId === null) {
            $gameId = Config::get('buffalo.game_id', 23);
        }
        
        // Provider requires lobbyUrl to be the game server URL (https://prime.next-api.net)
        // This is the base URL where the game will be loaded
        $providerLobbyUrl = $gameServerUrl;
        
        // Get client's website URL (for redirect when player exits)
        // Use provided lobbyUrl parameter or default from config
        $clientWebsiteUrl = $lobbyUrl ?: Config::get('buffalo.site.url', self::SITE_URL);
        
        // Prepare request payload (roomId must be string per provider API)
        $payload = [
            'uid' => $uid,
            'token' => $token,
            'gameId' => $gameId,
            'roomId' => (string) $roomId,  // Provider requires string, not integer
            'lobbyUrl' => $providerLobbyUrl,  // Provider's game server URL (https://prime.next-api.net)
            'domain' => $domain,  // Required by provider
        ];
        
        // Note: The provider API will return a URL like:
        // https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=https%3A%2F%2Fbuffaloking788.com
        // Where lobbyUrl query parameter contains the client's website URL for redirect
        
        Log::info('Buffalo Game Login API - Request', [
            'api_url' => $apiUrl,
            'user' => $user->user_name,
            'room_id' => $roomId,
            'game_id' => $gameId,
            'payload' => array_merge($payload, ['token' => substr($token, 0, 10) . '...']), // Log partial token
        ]);
        
        try {
            $response = Http::timeout($timeout)
                ->withOptions(['verify' => false]) // Disable SSL verification if needed
                ->asJson()
                ->post($apiUrl, $payload);
            
            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error('Buffalo Game Login API - Failed', [
                    'status' => $response->status(),
                    'response' => $errorBody,
                    'user' => $user->user_name,
                ]);
                
                throw new \Exception("Game Login API failed: HTTP {$response->status()} - {$errorBody}");
            }
            
            $responseData = $response->json();
            
            if (!isset($responseData['url'])) {
                Log::error('Buffalo Game Login API - Invalid response format', [
                    'response' => $responseData,
                    'user' => $user->user_name,
                ]);
                
                throw new \Exception("Game Login API returned invalid response: missing 'url' field");
            }
            
            $gameUrl = $responseData['url'];
            
            // Verify the returned URL format matches expected pattern
            // Expected: https://prime.next-api.net/?gameId=42&roomId=1&uid=...&token=...&lobbyUrl=https%3A%2F%2Fbuffaloking788.com
            if (!str_contains($gameUrl, 'prime.next-api.net')) {
                Log::warning('Buffalo Game Login API - Unexpected URL format', [
                    'user' => $user->user_name,
                    'game_url' => $gameUrl,
                ]);
            }
            
            Log::info('Buffalo Game Login API - Success', [
                'user' => $user->user_name,
                'room_id' => $roomId,
                'game_id' => $gameId,
                'game_url' => $gameUrl,
            ]);
            
            return $gameUrl;
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Buffalo Game Login API - Connection error', [
                'error' => $e->getMessage(),
                'user' => $user->user_name,
            ]);
            
            throw new \Exception("Failed to connect to Game Login API: " . $e->getMessage());
            
        } catch (\Exception $e) {
            Log::error('Buffalo Game Login API - Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => $user->user_name,
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate Buffalo game URL with lobby URL
     * This method now calls the provider's Game Login API
     * 
     * @param User $user
     * @param int $roomId Room ID (1-4)
     * @param string $lobbyUrl Lobby redirect URL
     * @param int|null $gameId Game ID (23 for normal, 42 for scatter). Default: 23
     * @return string Game URL from provider
     */
    public static function generateGameUrl(User $user, int $roomId = 1, string $lobbyUrl = '', ?int $gameId = null): string
    {
        return self::getGameUrlFromProvider($user, $roomId, $lobbyUrl, $gameId);
    }

    

    public static function getRoomConfig(): array
    {
        return [
            1 => ['min_bet' => 50, 'name' => '50 အခန်း', 'level' => 'Low'],
            2 => ['min_bet' => 500, 'name' => '500 အခန်း', 'level' => 'Medium'],
            3 => ['min_bet' => 5000, 'name' => '5000 အခန်း', 'level' => 'High'],
            4 => ['min_bet' => 10000, 'name' => '10000 အခန်း', 'level' => 'VIP'],
        ];
    }

    /**
     * Get available rooms for user based on balance
     */
    public static function getAvailableRooms(User $user): array
    {
        $userBalance = $user->balanceFloat; // Use bavix wallet trait
        $rooms = self::getRoomConfig();
        $availableRooms = [];

        foreach ($rooms as $roomId => $config) {
            if ($userBalance >= $config['min_bet']) {
                $availableRooms[$roomId] = $config;
            }
        }

        return $availableRooms;
    }

    public static function getSiteInfo(): array
    {
        return [
            'site_name' => self::SITE_NAME,
            'site_prefix' => self::SITE_PREFIX,
        ];
    }

    
}