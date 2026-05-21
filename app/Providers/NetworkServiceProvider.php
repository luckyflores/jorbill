<?php

namespace App\Providers;

use App\Services\Network\Contracts\GenieAcsClient;
use App\Services\Network\Contracts\MikrotikClient;
use App\Services\Network\Contracts\UispClient;
use App\Services\Network\Null\NullGenieAcsClient;
use App\Services\Network\Null\NullMikrotikClient;
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
                default => throw new RuntimeException('Unknown GenieACS driver: ' . config('network.genieacs.driver')),
            };
        });

        $this->app->bind(MikrotikClient::class, function () {
            return match (config('network.mikrotik.driver', 'null')) {
                'null' => new NullMikrotikClient(),
                default => throw new RuntimeException('Unknown Mikrotik driver: ' . config('network.mikrotik.driver')),
            };
        });
    }
}
