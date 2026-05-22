<?php

namespace Tests\Unit;

use App\Services\Notifications\Drivers\GlobeSmsNotifier;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GlobeSmsNotifierTest extends TestCase
{
    public function test_returns_null_when_no_token(): void
    {
        $n = new GlobeSmsNotifier(accessToken: null);
        $this->assertNull($n->send('09171234567', 'hello'));
    }

    public function test_m360_shape_uses_bearer_and_returns_id(): void
    {
        Http::fake([
            'api.m360.globe.com.ph/*' => Http::response(['message_id' => 'glb-001', 'status' => 'queued'], 200),
        ]);

        $n = new GlobeSmsNotifier(accessToken: 'tok-xyz');
        $ref = $n->send('09171234567', 'hello');

        $this->assertSame('glb-001', $ref);
        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer tok-xyz')
                && $request['to'] === '639171234567'
                && $request['message'] === 'hello'
                && $request['type'] === 'transactional';
        });
    }

    public function test_labs_shape_uses_access_token_query_and_envelope(): void
    {
        Http::fake([
            'devapi.globelabs.com.ph/*' => Http::response([
                'outboundSMSMessageRequest' => ['clientCorrelator' => 'lab-123'],
            ], 200),
        ]);

        $n = new GlobeSmsNotifier(
            accessToken: 'tok',
            endpoint: 'https://devapi.globelabs.com.ph',
            payloadShape: 'labs',
            shortcode: '8888',
        );
        $ref = $n->send('09171234567', 'hello');

        $this->assertSame('lab-123', $ref);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'access_token=tok')
                && str_contains($request->url(), '/outbound/8888/requests')
                && data_get($request->data(), 'outboundSMSMessageRequest.outboundSMSTextMessage.message') === 'hello';
        });
    }
}
