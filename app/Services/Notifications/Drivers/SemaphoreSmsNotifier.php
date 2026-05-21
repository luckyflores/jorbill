<?php

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreSmsNotifier implements Notifier
{
    public function __construct(
        private readonly ?string $apiKey,
        private readonly string $senderName = 'JorBill',
    ) {}

    public function id(): string { return 'semaphore'; }

    public function send(string $to, string $body, array $context = []): ?string
    {
        if (! $this->apiKey) {
            Log::warning('SemaphoreSmsNotifier::send skipped — no API key configured');
            return null;
        }

        $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
            'apikey' => $this->apiKey,
            'number' => $to,
            'message' => $body,
            'sendername' => $this->senderName,
        ]);

        if (! $response->successful()) {
            Log::error('SemaphoreSmsNotifier::send failed', [
                'status' => $response->status(), 'body' => $response->body(),
            ]);
            return null;
        }

        $data = $response->json();
        return $data[0]['message_id'] ?? null;
    }
}
