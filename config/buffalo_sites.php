<?php

$defaultProviderApiUrl = env('BUFFALO_API_URL', 'https://api-ms3.african-buffalo.club/api/game-login');
$defaultDomain = env('BUFFALO_DOMAIN', 'prime.com');
$defaultGameServerUrl = env('BUFFALO_GAME_SERVER_URL', 'https://prime.next-api.net');
$TTT_GameServerUrl = env('TTT_GAME_SERVER_URL', 'https://tttgamingmm.pro');
$OneXBet_GameServerUrl = env('OneXBet_GAME_SERVER_URL', 'https://m.onexbetmm.site');
$Burmese888_GameServerUrl = env('Burmese888_GAME_SERVER_URL', 'https://m.burmar888.site');
$Shanyoma_GameServerUrl = env('Shanyoma_GAME_SERVER_URL', 'https://m.shanyoma789.com');
$AZM999_GameServerUrl = env('AZM999_GAME_SERVER_URL', '');

$defaultGameId = env('BUFFALO_GAME_ID', 23);
$defaultApiTimeout = env('BUFFALO_API_TIMEOUT', 30);

return [
    /*
    |--------------------------------------------------------------------------
    | Buffalo Game Multi-Site Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration allows one centralized Buffalo API to serve multiple
    | websites/operators. Each site has a unique prefix for player identification.
    | https://ag.shanyoma789.com
    */

    'sites' => [
        // Site 1: PoneWine (Current Site)
        'sym' => [
            'name' => 'Shanyoma',
            'prefix' => 'sym',
            'site_url' => env('SITE_1_URL', 'https://ag.shanyoma789.com'),
            'api_url' => env('SITE_1_API_URL', 'https://ag.shanyoma789.com/api'),
            'lobby_url' => env('SITE_1_LOBBY_URL', $Shanyoma_GameServerUrl),
            'provider_api_url' => env('SITE_1_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_1_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_1_GAME_SERVER_URL', $Shanyoma_GameServerUrl),
            'game_id' => env('SITE_1_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_1_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_1_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

        // Site: MaxWin (mxm prefix used by provider default config)
        'mxm' => [
            'name' => 'MaxWin Myanmar',
            'prefix' => 'mxm',
            'site_url' => env('SITE_MXM_URL', 'https://maxwinmyanmar.pro'),
            'api_url' => env('SITE_MXM_API_URL', 'https://maxwinmyanmar.pro/api'),
            'lobby_url' => env('SITE_MXM_LOBBY_URL', 'https://maxwinmyanmar.pro'),
            'provider_api_url' => env('SITE_MXM_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_MXM_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_MXM_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_MXM_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_MXM_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_MXM_VERIFY_SSL', false),
            'is_local' => true,
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
            ],
            'enabled' => true,
        ],

        // Site 2: Golden City Casino
        'gcc' => [
            'name' => 'Golden City Casino',
            'prefix' => 'gcc',
            'site_url' => env('SITE_2_URL', 'https://ag.goldencitycasino123.site'),
            'api_url' => env('SITE_2_API_URL', 'https://ag.goldencitycasino123.site/api'),
            'lobby_url' => env('SITE_2_LOBBY_URL', 'https://ag.goldencitycasino123.site'),
            'provider_api_url' => env('SITE_2_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_2_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_2_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_2_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_2_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_2_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

         // Site 3: TTT Gaming MM
        'ttt' => [
            'name' => 'TTT Gaming MM',
            'prefix' => 'ttt',
            'site_url' => env('SITE_3_URL', 'https://ag.tttgamingmm.pro'),
            'api_url' => env('SITE_3_API_URL', 'https://ag.tttgamingmm.pro/api'),
            'lobby_url' => env('SITE_3_LOBBY_URL', $TTT_GameServerUrl),
            'provider_api_url' => env('SITE_3_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_3_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_3_GAME_SERVER_URL', $TTT_GameServerUrl),
            'game_id' => env('SITE_3_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_3_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_3_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

        // Site 4: OneXBet
        'oxb' => [
            'name' => 'OneXBet',
            'prefix' => 'oxb',
            'site_url' => env('SITE_4_URL', 'https://ag.onexbetmm.site'),
            'api_url' => env('SITE_4_API_URL', 'https://ag.onexbetmm.site/api'),
            'lobby_url' => env('SITE_4_LOBBY_URL', $OneXBet_GameServerUrl),
            'provider_api_url' => env('SITE_4_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_4_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_4_GAME_SERVER_URL', $OneXBet_GameServerUrl),
            'game_id' => env('SITE_4_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_4_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_4_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

         // Site 5: TryBet
        'tyb' => [
            'name' => 'TryBet',
            'prefix' => 'tyb',
            'site_url' => env('SITE_5_URL', 'https://ag.6tribet.net'),
            'api_url' => env('SITE_5_API_URL', 'https://ag.6tribet.net/api'),
            'lobby_url' => env('SITE_5_LOBBY_URL', 'https://ag.6tribet.net'),
            'provider_api_url' => env('SITE_5_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_5_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_5_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_5_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_5_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_5_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],


        // Site 6: GameStar
        'gm7' => [
            'name' => 'GameStar',
            'prefix' => 'gm7',
            'site_url' => env('SITE_6_URL', 'https://moneyking77.online'),
            'api_url' => env('SITE_6_API_URL', 'https://moneyking77.online/api'),
            'lobby_url' => env('SITE_6_LOBBY_URL', 'https://moneyking77.online/'),
            'provider_api_url' => env('SITE_6_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_6_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_6_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_6_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_6_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_6_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

        // Site 7: GameStar
        'closed_site' => [
            'name' => 'Meemeegamecenter',
            'prefix' => 'closed_site',
            'site_url' => env('SITE_7_URL', 'https://buffalo.meemeegamecenter.online'),
            'api_url' => env('SITE_7_API_URL', 'https://buffalo.meemeegamecenter.online/api'),
            'lobby_url' => env('SITE_7_LOBBY_URL', 'https://buffalo.meemeegamecenter.online/'),
            'provider_api_url' => env('SITE_7_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_7_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_7_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_7_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_7_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_7_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => false,
        ],


        // azm990 

        // 'az9' => [
        //     'name' => 'Azm999',
        //     'prefix' => 'az9',
        //     'site_url' => env('SITE_8_URL', 'https://master.azm999.com'),
        //     'api_url' => env('SITE_8_API_URL', 'https://master.azm999.com/api'),
        //     'lobby_url' => env('SITE_8_LOBBY_URL', 'https://master.azm999.com'),
        //     'provider_api_url' => env('SITE_8_PROVIDER_API_URL', $defaultProviderApiUrl),
        //     'domain' => env('SITE_8_DOMAIN', $defaultDomain),
        //     'game_server_url' => env('SITE_8_GAME_SERVER_URL', $defaultGameServerUrl),
        //     'game_id' => env('SITE_8_GAME_ID', $defaultGameId),
        //     'api_timeout' => env('SITE_8_API_TIMEOUT', $defaultApiTimeout),
        //     'verify_ssl' => env('SITE_8_VERIFY_SSL', false),
        //     'is_local' => false, // Forward to external API
        //     'api_endpoints' => [
        //         'get_balance' => '/buffalo/get-user-balance',
        //         'change_balance' => '/buffalo/change-balance',
        //         'launch_game' => '/buffalo/launch-game',
        //     ],
        //     'enabled' => true,
        // ],


        'az9' => [
            'name' => 'AZM999',
            'prefix' => 'az9',
            'site_url' => env('SITE_8_URL', 'https://master.azm999.com'),
            'api_url' => env('SITE_8_API_URL', 'https://master.azm999.com/api'),
            'lobby_url' => env('SITE_8_LOBBY_URL', $AZM999_GameServerUrl),
            'provider_api_url' => env('SITE_8_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_8_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_8_GAME_SERVER_URL', $AZM999_GameServerUrl),
            'game_id' => env('SITE_8_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_8_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_8_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],


        // Site 7: Example Site (Template for adding more)
        'exm' => [
            'name' => 'Example Site',
            'prefix' => 'exm',
            'site_url' => env('SITE_9_URL', 'https://example.com'),
            'api_url' => env('SITE_9_API_URL', 'https://example.com/api'),
            'lobby_url' => env('SITE_9_LOBBY_URL', 'https://example.com'),
            'provider_api_url' => env('SITE_9_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_9_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_9_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_9_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_9_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_9_VERIFY_SSL', false),
            'is_local' => false,
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => false, // Disabled by default
        ],


          // tg sawgyi 

        'mwy' => [
            'name' => 'TGSawgyi',
            'prefix' => 'mwy',
            'site_url' => env('SITE_10_URL', 'https://maxwinmyanmar.pro'),
            'api_url' => env('SITE_10_API_URL', 'https://maxwinmyanmar.pro/api'),
            'lobby_url' => env('SITE_10_LOBBY_URL', 'https://maxwinmyanmar.pro'),
            'provider_api_url' => env('SITE_10_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_10_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_10_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_10_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_10_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_10_VERIFY_SSL', false),
            'is_local' => true, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
            ],
            'enabled' => false,
        ],

         // shweshan kan 

        'shw' => [
            'name' => 'ShweShankan',
            'prefix' => 'shw',
            'site_url' => env('SITE_11_URL', 'https://shweshankan.com'),
            'api_url' => env('SITE_11_API_URL', 'https://shweshankan.com/api'),
            'lobby_url' => env('SITE_11_LOBBY_URL', 'https://shweshankan.com'),
            'provider_api_url' => env('SITE_11_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_11_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_11_GAME_SERVER_URL', $defaultGameServerUrl),
            'game_id' => env('SITE_11_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_11_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_11_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

        // burmese888

        'bm8' => [
            'name' => 'Burmese888',
            'prefix' => 'bm8',
            'site_url' => env('SITE_12_URL', 'https://ag.burmar888.online'),
            'api_url' => env('SITE_12_API_URL', 'https://ag.burmar888.online/api'),
            'lobby_url' => env('SITE_12_LOBBY_URL', $Burmese888_GameServerUrl),
            'provider_api_url' => env('SITE_12_PROVIDER_API_URL', $defaultProviderApiUrl),
            'domain' => env('SITE_12_DOMAIN', $defaultDomain),
            'game_server_url' => env('SITE_12_GAME_SERVER_URL', $Burmese888_GameServerUrl),
            'game_id' => env('SITE_12_GAME_ID', $defaultGameId),
            'api_timeout' => env('SITE_12_API_TIMEOUT', $defaultApiTimeout),
            'verify_ssl' => env('SITE_12_VERIFY_SSL', false),
            'is_local' => false, // Forward to external API
            'api_endpoints' => [
                'get_balance' => '/buffalo/get-user-balance',
                'change_balance' => '/buffalo/change-balance',
                'launch_game' => '/buffalo/launch-game',
            ],
            'enabled' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Default Site
    |--------------------------------------------------------------------------
    |
    | The default site to use when no prefix is detected or prefix is invalid
    |
    */
    'default_site' => 'pwf',

    /*
    |--------------------------------------------------------------------------
    | Room Configuration (Bet Limits)
    |--------------------------------------------------------------------------
    |
    | Different rooms with different bet limits
    |
    */
    'rooms' => [
        1 => [
            'name' => 'Room 50',
            'min_bet' => 50,
            'max_bet' => 500,
            'description' => 'Low stakes room',
        ],
        2 => [
            'name' => 'Room 500',
            'min_bet' => 500,
            'max_bet' => 5000,
            'description' => 'Medium stakes room',
        ],
        3 => [
            'name' => 'Room 5000',
            'min_bet' => 5000,
            'max_bet' => 50000,
            'description' => 'High stakes room',
        ],
        4 => [
            'name' => 'Room 10000',
            'min_bet' => 10000,
            'max_bet' => 100000,
            'description' => 'VIP room',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Buffalo Game Configuration
    |--------------------------------------------------------------------------
    |
    */
    'game_id' => 23, // Buffalo game ID
    'base_game_url' => env('BUFFALO_GAME_URL', 'http://prime7.wlkfkskakdf.com/'),
];

