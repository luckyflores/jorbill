<?php

namespace Tests\Unit;

use App\Models\NotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_semaphore_delivered_updates_log(): void
    {
        $log = NotificationLog::create([
            'channel' => 'sms', 'driver' => 'semaphore', 'to' => '63917',
            'body' => 'hi', 'status' => 'sent', 'gateway_reference' => 'msg-xyz',
        ]);

        $r = $this->postJson('/webhooks/sms/semaphore', [
            'message_id' => 'msg-xyz',
            'status'     => 'Delivered',
        ]);

        $r->assertOk();
        $log->refresh();
        $this->assertSame('sent', $log->status);
        $this->assertNotNull($log->delivered_at);
        $this->assertSame('Delivered', $log->provider_status);
    }

    public function test_semaphore_failed_marks_failed(): void
    {
        $log = NotificationLog::create([
            'channel' => 'sms', 'driver' => 'semaphore', 'to' => '63917',
            'body' => 'hi', 'status' => 'sent', 'gateway_reference' => 'msg-err',
        ]);

        $this->postJson('/webhooks/sms/semaphore', [
            'message_id' => 'msg-err', 'status' => 'Failed', 'error_code' => 'INSUFFICIENT_CREDITS',
        ])->assertOk();

        $log->refresh();
        $this->assertSame('failed', $log->status);
        $this->assertSame('INSUFFICIENT_CREDITS', $log->provider_error_code);
    }

    public function test_unknown_reference_returns_ok_with_note(): void
    {
        $this->postJson('/webhooks/sms/semaphore', ['message_id' => 'no-such', 'status' => 'Delivered'])
            ->assertOk()
            ->assertJson(['ok' => true]);
    }
}
