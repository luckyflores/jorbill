<?php

namespace App\Providers;

use App\Services\Notifications\Contracts\Notifier;
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
                default => throw new RuntimeException('Unknown notifier: ' . config('notifications.default')),
            };
        });
    }
}
