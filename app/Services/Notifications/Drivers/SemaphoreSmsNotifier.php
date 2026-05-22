<?php

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Semaphore SMS (PH) — REST POST against https://api.semaphore.co/api/v4/messages
 *
 * Auth: form field 'apikey'
 * Required: number, message
 * Optional: sendername (must be pre-registered with Semaphore)
 *
 * Response: JSON array, each entry has message_id, recipient, status, etc.
 *
 * Pricing/status: Semaphore returns "Pending" immediately; "Sent"/"Failed" via webhook callback.
 */
class SemaphoreSmsNotifier implements Notifier
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $senderName = 'JorBill',
        private readonly string $baseUrl = 'https://api.semaphore.co/api/v4',
        private readonly int $timeout = 8,
    ) {}

    public function id(): string { return 'semaphore'; }

    public function send(string $to, string $body, array $context = []): ?string
    {
        if (! $this->apiKey) {
            Log::warning('SemaphoreSmsNotifier::send skipped — no SEMAPHORE_API_KEY configured');
            return null;
        }

        try {
            $response = Http::asForm()->timeout($this->timeout)->post("{$this->baseUrl}/messages", [
                'apikey'     => $this->apiKey,
                'number'     => $this->normalizePhone($to),
                'message'    => $body,
                'sendername' => $this->senderName,
            ]);

            if (! $response->successful()) {
                Log::error('SemaphoreSmsNotifier: HTTP error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                    'to'     => $to,
                ]);
                return null;
            }

            $data = $response->json();
            if (! is_array($data) || empty($data)) {
                Log::warning('SemaphoreSmsNotifier: unexpected response shape', ['body' => $response->body()]);
                return null;
            }

            $entry = is_array($data[0] ?? null) ? $data[0] : $data;
            return (string) ($entry['message_id'] ?? '') ?: null;
        } catch (Throwable $e) {
            Log::error('SemaphoreSmsNotifier::send threw', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /** Strip non-digits, ensure starts with country code (09xx → 639xx for PH). */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0')) {
            return '63' . substr($digits, 1);
        }
        return $digits;
    }
}
