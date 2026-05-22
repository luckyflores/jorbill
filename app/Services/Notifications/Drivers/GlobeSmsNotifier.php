<?php

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Globe Labs / Globe M360 Enterprise SMS API.
 *
 * Two common shapes — driver is configurable to handle either:
 *
 * 1. Classic Globe Labs (shortcode-based, OAuth access_token):
 *    POST https://devapi.globelabs.com.ph/smsmessaging/v1/outbound/{shortcode}/requests?access_token=...
 *    body: {"outboundSMSMessageRequest":{"address":"tel:+639XXX","senderAddress":"shortcode","outboundSMSTextMessage":{"message":"..."}}}
 *
 * 2. M360 / newer bearer-auth flat shape:
 *    POST https://api.m360.globe.com.ph/sms/send
 *    Authorization: Bearer <token>
 *    body: {"to":"+639XXX","from":"SENDER","message":"...","type":"transactional"}
 *
 * Defaults to shape 2 (M360). Set GLOBE_PAYLOAD_SHAPE=labs in .env to use shape 1.
 */
class GlobeSmsNotifier implements Notifier
{
    public function __construct(
        private readonly ?string $accessToken,
        private readonly string $endpoint = 'https://api.m360.globe.com.ph/sms/send',
        private readonly string $senderName = 'JORBILL',
        private readonly string $payloadShape = 'm360',     // 'm360' or 'labs'
        private readonly ?string $shortcode = null,         // required for 'labs' shape
        private readonly int $timeout = 8,
    ) {}

    public function id(): string { return 'globe'; }

    public function send(string $to, string $body, array $context = []): ?string
    {
        if (! $this->accessToken) {
            Log::warning('GlobeSmsNotifier::send skipped — no GLOBE_ACCESS_TOKEN configured');
            return null;
        }

        $normalized = $this->normalizePhone($to);

        try {
            $response = $this->payloadShape === 'labs'
                ? $this->sendLabsShape($normalized, $body)
                : $this->sendM360Shape($normalized, $body);

            if (! $response->successful()) {
                Log::error('GlobeSmsNotifier: HTTP error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 500),
                    'to'     => $to,
                ]);
                return null;
            }

            $data = $response->json() ?? [];
            // Both shapes return some kind of message_id/reference; try common keys
            return (string) (
                $data['message_id']
                ?? $data['messageId']
                ?? $data['outboundSMSMessageRequest']['clientCorrelator']
                ?? $data['reference']
                ?? ''
            ) ?: null;
        } catch (Throwable $e) {
            Log::error('GlobeSmsNotifier::send threw', ['error' => $e->getMessage()]);
            return null;
        }
    }

    private function sendM360Shape(string $to, string $body): \Illuminate\Http\Client\Response
    {
        return Http::withToken($this->accessToken)
            ->timeout($this->timeout)
            ->acceptJson()
            ->post($this->endpoint, [
                'to'      => $to,
                'from'    => $this->senderName,
                'message' => $body,
                'type'    => 'transactional',
            ]);
    }

    private function sendLabsShape(string $to, string $body): \Illuminate\Http\Client\Response
    {
        $url = rtrim($this->endpoint, '/');
        if ($this->shortcode && ! str_contains($url, '/requests')) {
            $url .= "/smsmessaging/v1/outbound/{$this->shortcode}/requests";
        }
        $url .= '?access_token=' . urlencode($this->accessToken);

        return Http::timeout($this->timeout)->acceptJson()->post($url, [
            'outboundSMSMessageRequest' => [
                'address'                => 'tel:+' . $to,
                'senderAddress'          => $this->shortcode ?? $this->senderName,
                'outboundSMSTextMessage' => ['message' => $body],
            ],
        ]);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (str_starts_with($digits, '0')) {
            return '63' . substr($digits, 1);
        }
        return $digits;
    }
}
