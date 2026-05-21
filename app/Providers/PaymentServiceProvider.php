<?php

namespace App\Providers;

use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Null\NullPaymentGateway;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            return match (config('payment.default', 'null')) {
                'null' => new NullPaymentGateway(),
                // 'xendit' => new \App\Services\Payment\Xendit\XenditGateway(...), // TODO Phase 3
                default => throw new RuntimeException('Unknown payment gateway: ' . config('payment.default')),
            };
        });
    }
}
