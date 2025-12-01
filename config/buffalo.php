<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Buffalo Game Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Buffalo game provider integration
    |
    */

    // Game Login API URLs
    'api' => [
        'url' => env('BUFFALO_API_URL', 'https://api-ms3.african-buffalo.club/api/game-login'),
    ],

    // Game types
    'game_types' => [
        'normal' => 23,      // Normal Buffalo game
        'scatter' => 42,     // Scatter Buffalo game
    ],

    // Site configuration
    'site' => [
        'name' => env('BUFFALO_SITE_NAME', 'https://maxwinmyanmar.pro'),
        'prefix' => env('BUFFALO_SITE_PREFIX', 'mxm'),
        'url' => env('BUFFALO_SITE_URL', 'https://maxwinmyanmar.pro'),
    ],

    // Game server URL (provider's lobby URL)
    'game_server_url' => env('BUFFALO_GAME_SERVER_URL', ''), //https://prime.next-api.net

    // Domain name provided by provider (REQUIRED for Game Login API)
    'domain' => env('BUFFALO_DOMAIN', 'prime.com'),

    // Game ID
    'game_id' => env('BUFFALO_GAME_ID', 23),

    // Request timeout in seconds
    'timeout' => env('BUFFALO_API_TIMEOUT', 30),
];

