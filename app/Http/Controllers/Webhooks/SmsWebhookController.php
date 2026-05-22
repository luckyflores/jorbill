<?php

namespace App\Http\Controllers\Webhooks;

use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SmsWebhookController
{
    /**
     * Provider-specific delivery callback handler.
     * Updates NotificationLog.delivered_at + provider_status based on the provider's payload.
     */
    public function handle(Request $request, string $provider)
    {
        $payload = $request->all();
        Log::info('SmsWebhookController: incoming', ['provider' => $provider, 'payload' => $payload]);

        [$messageId, $status, $errorCode] = match ($provider) {
            'semaphore' => $this->parseSemaphore($payload),
            'globe'     => $this->parseGlobe($payload),
            default     => [null, null, null],
        };

        if (! $messageId) {
            return response()->json(['ok' => true, 'note' => 'message id not found in payload'], 200);
        }

        $log = NotificationLog::where('gateway_reference', $messageId)->first();
        if (! $log) {
            Log::info('SmsWebhookController: no NotificationLog for reference', compact('messageId'));
            return response()->json(['ok' => true, 'note' => 'no matching log row'], 200);
        }

        $updates = ['provider_status' => $status];
        $statusLower = strtolower((string) $status);

        if (in_array($statusLower, ['delivered', 'sent', 'success', 'ok'], true)) {
            $updates['delivered_at'] = now();
            $updates['status'] = 'sent';
        } elseif (in_array($statusLower, ['failed', 'error', 'rejected', 'undelivered'], true)) {
            $updates['status'] = 'failed';
            $updates['provider_error_code'] = $errorCode;
        }

        $log->update($updates);
        return response()->json(['ok' => true, 'updated' => $log->id]);
    }

    /** Semaphore status callback: {message_id, recipient, status, ...} */
    private function parseSemaphore(array $payload): array
    {
        return [
            $payload['message_id'] ?? null,
            $payload['status']     ?? null,
            $payload['error_code'] ?? null,
        ];
    }

    /** Globe callback shape varies between Labs and M360. Try common keys. */
    private function parseGlobe(array $payload): array
    {
        $msgId  = $payload['message_id'] ?? $payload['messageId']
               ?? $payload['outboundSMSMessageRequest']['clientCorrelator'] ?? null;
        $status = $payload['delivery_status'] ?? $payload['deliveryStatus']
               ?? $payload['status'] ?? null;
        $err    = $payload['error_code'] ?? $payload['errorCode'] ?? null;
        return [$msgId, $status, $err];
    }
}
