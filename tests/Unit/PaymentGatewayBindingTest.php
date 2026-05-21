<?php

namespace Tests\Unit;

use App\Services\Payment\Contracts\PaymentGateway;
use App\Services\Payment\Null\NullPaymentGateway;
use Tests\TestCase;

class PaymentGatewayBindingTest extends TestCase
{
    public function test_payment_gateway_resolves_to_null_by_default(): void
    {
        $this->assertInstanceOf(NullPaymentGateway::class, app(PaymentGateway::class));
        $this->assertSame('null', app(PaymentGateway::class)->id());
    }
}
