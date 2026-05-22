<?php

namespace App\Providers;

use App\Services\Notifications\Contracts\Notifier;
use App\Services\Notifications\Drivers\GlobeSmsNotifier;
use App\Services\Notifications\Drivers\LogNotifier;
use App\Services\Notifications\Drivers\NullNotifier;
use App\Services\Notifications\Drivers\SemaphoreSmsNotifier;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Notifier::class, function () {
            return match (config('notifications.default', 'log')) {
                'null'      => new NullNotifier(),
                'log'       => new LogNotifier(),
                'semaphore' => new SemaphoreSmsNotifier(
                    apiKey: config('notifications.semaphore.api_key'),
                    senderName: config('notifications.semaphore.sender_name', 'JorBill'),
                ),
                'globe'     => new GlobeSmsNotifier(
                    accessToken: config('notifications.globe.access_token'),
                    endpoint:    config('notifications.globe.endpoint', 'https://api.m360.globe.com.ph/sms/send'),
                    senderName:  config('notifications.globe.sender_name', 'JORBILL'),
                    payloadShape:config('notifications.globe.payload_shape', 'm360'),
                    shortcode:   config('notifications.globe.shortcode'),
                ),
                default => throw new RuntimeException('Unknown notifier: ' . config('notifications.default')),
            };
        });
    }
}
