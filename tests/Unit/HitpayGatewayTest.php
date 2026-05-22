<?php

namespace Tests\Unit;

use App\Services\Payment\Hitpay\HitpayGateway;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HitpayGatewayTest extends TestCase
{
    public function test_create_checkout_returns_url_and_reference(): void
    {
        Http::fake([
            '*/payment-requests' => Http::response([
                'id'     => 'pr_abc123',
                'url'    => 'https://api.sandbox.hit-pay.com/checkout/pr_abc123',
                'status' => 'pending',
            ], 200),
        ]);

        $g = new HitpayGateway(apiKey: 'k', salt: 's', useLive: false);
        $r = $g->createCheckout(99900, 'SI-1', ['name' => 'J', 'email' => 'a@b.c', 'phone' => '0917'], 'https://x/return');
        $this->assertSame('https://api.sandbox.hit-pay.com/checkout/pr_abc123', $r['checkout_url']);
        $this->assertSame('pr_abc123', $r['gateway_reference']);
    }

    public function test_webhook_invalid_hmac_returns_null(): void
    {
        $g = new HitpayGateway('k', 's', false);
        $payload = [
            'payment_id'         => 'p_xyz',
            'payment_request_id' => 'pr_abc',
            'amount'             => '999.00',
            'currency'           => 'PHP',
            'status'             => 'completed',
            'reference_number'   => 'SI-1',
            'hmac'               => 'wrong-signature',
        ];
        $this->assertNull($g->handleWebhook($payload));
    }

    public function test_webhook_valid_hmac_normalizes_payload(): void
    {
        $salt = 'my-salt';
        $g = new HitpayGateway('k', $salt, false);

        $payload = [
            'payment_id'         => 'p_xyz',
            'payment_request_id' => 'pr_abc',
            'amount'             => '999.00',
            'currency'           => 'PHP',
            'status'             => 'completed',
            'reference_number'   => 'SI-1',
        ];
        // Build a valid hmac the way HitPay does: sort, concat key+value, sign
        $toSign = $payload;
        ksort($toSign);
        $data = '';
        foreach ($toSign as $k => $v) $data .= $k . $v;
        $payload['hmac'] = hash_hmac('sha256', $data, $salt);

        $result = $g->handleWebhook($payload);
        $this->assertNotNull($result);
        $this->assertSame('completed', $result['status']);
        $this->assertSame(99900,       $result['amount_centavos']);
        $this->assertSame('SI-1',      $result['reference_number']);
        $this->assertSame('pr_abc',    $result['gateway_reference']);
    }
}
