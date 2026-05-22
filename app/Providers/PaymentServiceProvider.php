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
                'hitpay' => new \App\Services\Payment\Hitpay\HitpayGateway(
                    apiKey:  (string) config('payment.gateways.hitpay.api_key'),
                    salt:    (string) config('payment.gateways.hitpay.salt'),
                    useLive: (bool)   config('payment.gateways.hitpay.use_live', false),
                    currency:(string) config('payment.gateways.hitpay.currency', 'PHP'),
                ),
                // 'xendit' => new \App\Services\Payment\Xendit\XenditGateway(...), // TODO Phase 3
                default => throw new RuntimeException('Unknown payment gateway: ' . config('payment.default')),
            };
        });
    }
}
