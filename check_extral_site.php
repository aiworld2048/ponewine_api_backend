<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

/**
 * Buffalo External Site Checker
 *
 * Quickly review the configuration for partner (non-local) sites and optionally
 * ping their Buffalo API endpoints to confirm connectivity.
 *
 * Usage:
 *   php check_extral_site.php                # List every external site
 *   php check_extral_site.php gcc            # Inspect a single prefix
 *   php check_extral_site.php gcc --ping     # Inspect + ping endpoints
 *   php check_extral_site.php --ping         # Ping all external sites
 */

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$options = parseArguments($argv);
$sites = Config::get('buffalo_sites.sites', []);

if (empty($sites)) {
    fwrite(STDERR, "No sites configured in config/buffalo_sites.php\n");
    exit(1);
}

$externalSites = array_filter($sites, static fn ($site) => !($site['is_local'] ?? false));

if (empty($externalSites)) {
    fwrite(STDOUT, "All configured sites are marked as local.\n");
    exit(0);
}

if ($options['prefix'] !== null) {
    if (!isset($externalSites[$options['prefix']])) {
        fwrite(STDERR, "Site prefix '{$options['prefix']}' not found or is not external.\n");
        exit(1);
    }
    $externalSites = [$options['prefix'] => $externalSites[$options['prefix']]];
}

echo PHP_EOL;
echo "========================================" . PHP_EOL;
echo "Buffalo External Site Checker" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

foreach ($externalSites as $prefix => $site) {
    renderSiteSummary($prefix, $site);

    if ($options['ping']) {
        pingEndpoints($prefix, $site);
    }

    echo str_repeat('-', 40) . PHP_EOL . PHP_EOL;
}

/**
 * Parse CLI arguments.
 */
function parseArguments(array $argv): array
{
    $options = [
        'prefix' => null,
        'ping' => false,
    ];

    foreach (array_slice($argv, 1) as $arg) {
        if ($arg === '--ping') {
            $options['ping'] = true;
            continue;
        }

        if (str_starts_with($arg, '--prefix=')) {
            $options['prefix'] = substr($arg, strlen('--prefix='));
            continue;
        }

        // First non-flag argument is treated as prefix
        if ($options['prefix'] === null) {
            $options['prefix'] = $arg;
        }
    }

    return $options;
}

/**
 * Print site summary block.
 */
function renderSiteSummary(string $prefix, array $site): void
{
    $enabled = ($site['enabled'] ?? false) ? 'Yes' : 'No';
    $apiUrl = $site['api_url'] ?? 'N/A';
    $providerApiUrl = $site['provider_api_url'] ?? Config::get('buffalo.api.url');
    $domain = $site['domain'] ?? Config::get('buffalo.domain');
    $gameServerUrl = $site['game_server_url'] ?? Config::get('buffalo.game_server_url');
    $gameId = $site['game_id'] ?? Config::get('buffalo.game_id');
    $timeout = $site['api_timeout'] ?? Config::get('buffalo.timeout', 10);

    echo "Prefix        : {$prefix}" . PHP_EOL;
    echo "Name          : " . ($site['name'] ?? 'Unknown') . PHP_EOL;
    echo "Enabled       : {$enabled}" . PHP_EOL;
    echo "API URL       : {$apiUrl}" . PHP_EOL;
    echo "Provider API  : {$providerApiUrl}" . PHP_EOL;
    echo "Domain        : {$domain}" . PHP_EOL;
    echo "Lobby URL     : " . ($site['lobby_url'] ?? 'N/A') . PHP_EOL;
    echo "Game Server   : {$gameServerUrl}" . PHP_EOL;
    echo "Game ID       : {$gameId}" . PHP_EOL;
    echo "API Timeout   : {$timeout} sec" . PHP_EOL;
    echo "Verify SSL    : " . ((bool) ($site['verify_ssl'] ?? false) ? 'Yes' : 'No') . PHP_EOL;
}

/**
 * Attempt to call remote API endpoints for a site.
 */
function pingEndpoints(string $prefix, array $site): void
{
    $endpoints = $site['api_endpoints'] ?? [];

    if (empty($endpoints)) {
        echo "Ping          : Skipped (no api_endpoints configured)" . PHP_EOL;
        return;
    }

    echo "Ping          :" . PHP_EOL;

    foreach ($endpoints as $key => $path) {
        $url = buildEndpointUrl($site, $path);

        if (!$url) {
            echo "  - {$key}: Missing api_url/path" . PHP_EOL;
            continue;
        }

        $payload = buildSamplePayload($key, $site);
        $verifySsl = (bool) ($site['verify_ssl'] ?? false);
        $timeout = $site['api_timeout'] ?? Config::get('buffalo.timeout', 10);

        try {
            $response = Http::timeout($timeout)
                ->withOptions(['verify' => $verifySsl])
                ->asForm()
                ->post($url, $payload);

            $status = $response->status();
            $msg = "HTTP {$status}";

            if ($response->successful()) {
                $msg .= ' (reachable)';
            } else {
                $msg .= ' (error response)';
            }

            echo "  - {$key}: {$msg}" . PHP_EOL;
        } catch (\Throwable $e) {
            echo "  - {$key}: Connection failed ({$e->getMessage()})" . PHP_EOL;
        }
    }
}

/**
 * Build a fully qualified endpoint URL.
 */
function buildEndpointUrl(array $site, string $path): ?string
{
    $base = $site['api_url'] ?? null;

    if (!$base) {
        return null;
    }

    return rtrim($base, '/') . $path;
}

/**
 * Create a sample payload for health-check requests.
 */
function buildSamplePayload(string $endpointKey, array $site): array
{
    $gameId = $site['game_id'] ?? Config::get('buffalo.game_id', 23);

    return match ($endpointKey) {
        'get_balance' => [
            'uid' => 'health-check-uid',
            'token' => 'health-check-token',
        ],
        'change_balance' => [
            'uid' => 'health-check-uid',
            'token' => 'health-check-token',
            'changemoney' => 0,
            'bet' => 0,
            'win' => 0,
            'gameId' => $gameId,
        ],
        default => [
            'uid' => 'health-check-uid',
            'token' => 'health-check-token',
        ],
    };
}

if (!function_exists('str_starts_with')) {
    /**
     * Polyfill for PHP < 8.
     */
    function str_starts_with(string $haystack, string $needle): bool
    {
        return (string) $needle !== '' && strpos($haystack, $needle) === 0;
    }
}

