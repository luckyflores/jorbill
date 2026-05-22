<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with(config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        \App\Models\Router::observe(\App\Observers\RouterObserver::class);
        \App\Models\Subscription::observe(\App\Observers\SubscriptionObserver::class);
        \Illuminate\Support\Facades\Event::listen(
            \App\Events\PaymentRecorded::class,
            \App\Listeners\SendPaymentReceipt::class,
        );
        //
    }
}
