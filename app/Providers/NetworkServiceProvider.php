<?php

namespace App\Providers;

use App\Services\Network\Contracts\GenieAcsClient;
use App\Services\Network\Contracts\MikrotikClient;
use App\Services\Network\Contracts\MikrotikClientFactory;
use App\Services\Network\Contracts\UispClient;
use App\Services\Network\Live\LiveGenieAcsClient;
use App\Services\Network\Contracts\OltClientFactory;
use App\Services\Network\Live\LiveMikrotikClientFactory;
use App\Services\Network\Live\LiveOltClientFactory;
use App\Services\Network\Null\NullOltClientFactory;
use App\Services\Network\Null\NullGenieAcsClient;
use App\Services\Network\Null\NullMikrotikClient;
use App\Services\Network\Null\NullMikrotikClientFactory;
use App\Services\Network\Null\NullUispClient;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class NetworkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UispClient::class, function () {
            return match (config('network.uisp.driver', 'null')) {
                'null' => new NullUispClient(),
                default => throw new RuntimeException('Unknown UISP driver: ' . config('network.uisp.driver')),
            };
        });

        $this->app->bind(GenieAcsClient::class, function () {
            return match (config('network.genieacs.driver', 'null')) {
                'null' => new NullGenieAcsClient(),
                'live' => new LiveGenieAcsClient(
                    baseUrl: config('network.genieacs.base_url') ?? 'http://127.0.0.1:7557',
                    username: config('network.genieacs.username'),
                    password: config('network.genieacs.password'),
                ),
                default => throw new RuntimeException('Unknown GenieACS driver: ' . config('network.genieacs.driver')),
            };
        });

        // Generic, router-less binding (kept for backward compat with Phase 1 stub usage)
        $this->app->bind(MikrotikClient::class, function () {
            return new NullMikrotikClient();
        });

        // Per-router factory ? this is what production code should use
        
        $this->app->bind(OltClientFactory::class, function () {
            return match (config('network.olt.driver', 'null')) {
                'null' => new NullOltClientFactory(),
                'live' => new LiveOltClientFactory(),
                default => throw new RuntimeException('Unknown OLT driver: ' . config('network.olt.driver')),
            };
        });

        $this->app->bind(MikrotikClientFactory::class, function () {
            return match (config('network.mikrotik.driver', 'null')) {
                'null' => new NullMikrotikClientFactory(),
                'live' => new LiveMikrotikClientFactory(),
                default => throw new RuntimeException('Unknown Mikrotik driver: ' . config('network.mikrotik.driver')),
            };
        });
    }
}
