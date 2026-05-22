<?php

namespace App\Providers;

use App\Services\Notifications\Contracts\Notifier;
use App\Services\Notifications\Drivers\GlobeSmsNotifier;
use App\Services\Notifications\Drivers\LogNotifier;
use App\Services\Notifications\Drivers\NullNotifier;
use App\Services\Notifications\Drivers\SemaphoreSmsNotifier;
use App\Services\Notifications\NotifierRegistry;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotifierRegistry::class, function () {
            $registry = new NotifierRegistry();

            // Always-available debug channels
            $registry->register(new NullNotifier());
            $registry->register(new LogNotifier());

            // Semaphore — always registered so it can be selected per-rule;
            // send() returns null at runtime if the API key is not configured.
            $registry->register(new SemaphoreSmsNotifier(
                apiKey: config('notifications.semaphore.api_key'),
                senderName: config('notifications.semaphore.sender_name', 'JorBill'),
            ));

            // Globe — same: registered always, runtime no-op without token
            $registry->register(new GlobeSmsNotifier(
                accessToken: config('notifications.globe.access_token'),
                endpoint:    config('notifications.globe.endpoint', 'https://api.m360.globe.com.ph/sms/send'),
                senderName:  config('notifications.globe.sender_name', 'JORBILL'),
                payloadShape:config('notifications.globe.payload_shape', 'm360'),
                shortcode:   config('notifications.globe.shortcode'),
            ));

            return $registry;
        });

        // Default Notifier — still env-driven, but now resolved through the registry
        $this->app->bind(Notifier::class, function ($app) {
            $default = config('notifications.default', 'log');
            return $app->make(NotifierRegistry::class)->forChannel($default)
                ?? new LogNotifier();
        });
    }
}
