<?php

namespace App\Providers;

use App\Services\Automation\AutomationEngine;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AutomationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AutomationEngine::class);
    }

    public function boot(): void
    {
        // subscribe to every Eloquent model event; the engine filters internally
        Event::listen('eloquent.*', function (string $eventName, array $payload) {
            // skip during migrations / artisan commands to avoid noise
            if (app()->runningInConsole() && ! app()->environment('testing')) {
                // still let it run for queue worker, but skip during migrate / db:seed
                if (in_array($_SERVER['argv'][1] ?? '', ['migrate', 'migrate:fresh', 'db:seed', 'migrate:rollback'], true)) {
                    return;
                }
            }
            app(AutomationEngine::class)->onModelEvent($eventName, $payload);
        });
    }
}
