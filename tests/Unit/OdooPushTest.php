<?php

namespace Tests\Unit;

use App\Services\Odoo\Live\LiveOdooClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OdooPushTest extends TestCase
{
    private function fakeAuth(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => ['uid' => 2]], 200, ['Set-Cookie' => 'session_id=s; Path=/']),
        ]);
    }

    public function test_push_invoice_creates_then_posts(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => ['uid' => 2]], 200, ['Set-Cookie' => 'session_id=s; Path=/']),
            '*/web/dataset/call_kw' => Http::sequence()
                ->push(['result' => 88], 200)     // create returns id
                ->push(['result' => true], 200),  // action_post returns true
        ]);

        $c = new LiveOdooClient('http://o', 'db', 'admin', 'pw');
        $id = $c->pushInvoice(
            ['invoice_number' => 'SI-1', 'issued_at' => '2026-05-01', 'due_at' => '2026-05-15'],
            [['description' => 'Fiber 50M', 'quantity' => 1, 'unit_price_centavos' => 99900]],
            partnerId: 42,
        );
        $this->assertSame(88, $id);

        Http::assertSentCount(3);
    }

    public function test_push_payment_creates_then_posts(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => ['uid' => 2]], 200, ['Set-Cookie' => 'session_id=s; Path=/']),
            '*/web/dataset/call_kw' => Http::sequence()
                ->push(['result' => 77], 200)
                ->push(['result' => true], 200),
        ]);

        $c = new LiveOdooClient('http://o', 'db', 'admin', 'pw');
        $id = $c->pushPayment(
            ['payment_number' => 'PMT-1', 'amount_centavos' => 99900, 'received_at' => '2026-05-10'],
            partnerId: 42,
        );
        $this->assertSame(77, $id);
    }

    public function test_push_payment_negative_amount_uses_outbound(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => ['uid' => 2]], 200, ['Set-Cookie' => 'session_id=s; Path=/']),
            '*/web/dataset/call_kw' => Http::sequence()
                ->push(['result' => 78], 200)
                ->push(['result' => true], 200),
        ]);

        $c = new LiveOdooClient('http://o', 'db', 'admin', 'pw');
        $c->pushPayment(['payment_number' => 'REV-1', 'amount_centavos' => -99900], 42);

        Http::assertSent(function ($req) {
            $body = json_decode($req->body(), true);
            if (! isset($body['params']['method']) || $body['params']['method'] !== 'create') return false;
            $vals = $body['params']['args'][0];
            // loose compare — PHP's / on whole numbers returns int, JSON has no int/float distinction
            return $vals['payment_type'] === 'outbound' && $vals['amount'] == 999;
        });
    }

    public function test_cancel_payment_returns_true_on_success(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => ['uid' => 2]], 200, ['Set-Cookie' => 'session_id=s; Path=/']),
            '*/web/dataset/call_kw' => Http::response(['result' => true], 200),
        ]);

        $c = new LiveOdooClient('http://o', 'db', 'admin', 'pw');
        $this->assertTrue($c->cancelPayment(123));
    }
}
