<?php

namespace Tests\Unit;

use App\Services\Notifications\Drivers\SemaphoreSmsNotifier;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SemaphoreSmsNotifierTest extends TestCase
{
    public function test_returns_null_when_api_key_missing(): void
    {
        $n = new SemaphoreSmsNotifier(apiKey: null);
        $this->assertNull($n->send('09171234567', 'hello'));
    }

    public function test_normalizes_phone_and_returns_message_id(): void
    {
        Http::fake([
            'api.semaphore.co/*' => Http::response([
                ['message_id' => 'abc123', 'recipient' => '639171234567', 'status' => 'Pending'],
            ], 200),
        ]);

        $n = new SemaphoreSmsNotifier(apiKey: 'test-key');
        $ref = $n->send('09171234567', 'hello');

        $this->assertSame('abc123', $ref);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.semaphore.co/api/v4/messages'
                && $request['number'] === '639171234567'
                && $request['message'] === 'hello'
                && $request['apikey'] === 'test-key';
        });
    }

    public function test_returns_null_on_http_error(): void
    {
        Http::fake(['api.semaphore.co/*' => Http::response('rate limited', 429)]);
        $n = new SemaphoreSmsNotifier(apiKey: 'k');
        $this->assertNull($n->send('09171234567', 'hi'));
    }
}
