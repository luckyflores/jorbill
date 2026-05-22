<?php

namespace App\Providers;

use App\Services\Odoo\Contracts\OdooClient;
use App\Services\Odoo\Live\LiveOdooClient;
use App\Services\Odoo\Null\NullOdooClient;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class OdooServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(OdooClient::class, function () {
            return match (config('odoo.driver', 'null')) {
                'null' => new NullOdooClient(),
                'live' => new LiveOdooClient(
                    baseUrl:  config('odoo.base_url'),
                    db:       config('odoo.db'),
                    login:    config('odoo.login'),
                    password: config('odoo.password'),
                ),
                default => throw new RuntimeException('Unknown Odoo driver: ' . config('odoo.driver')),
            };
        });
    }
}
