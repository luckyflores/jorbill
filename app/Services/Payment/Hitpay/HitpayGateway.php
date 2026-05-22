<?php

namespace App\Services\Payment\Hitpay;

use App\Services\Payment\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * HitPay (sg/ph) hosted-checkout payment gateway.
 *
 * Flow:
 *  1. createCheckout(invoice, customer) → POST /v1/payment-requests
 *     - HitPay returns { id, url, status:pending }
 *     - We give the customer `url` (SMS / email)
 *  2. Customer pays on HitPay's hosted page
 *  3. HitPay POSTs to webhook_url with payload (incl. HMAC)
 *  4. handleWebhook() validates HMAC + returns normalized result
 *
 * HMAC scheme (HitPay docs):
 *  - sort payload by key, exclude 'hmac' itself
 *  - concatenate: key1value1key2value2...
 *  - HMAC-SHA256 with merchant salt
 *  - compare to received hmac field (hex)
 */
class HitpayGateway implements PaymentGateway
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $salt,
        private readonly bool   $useLive = false,
        private readonly string $currency = 'PHP',
    ) {}

    public function id(): string { return 'hitpay'; }

    public function supportedMethods(): array
    {
        return ['card', 'gcash', 'paymaya', 'paynow', 'fps', 'grabpay'];
    }

    private function baseUrl(): string
    {
        return $this->useLive
            ? 'https://api.hit-pay.com/v1'
            : 'https://api.sandbox.hit-pay.com/v1';
    }

    /**
     * @param array  $customer    keyed: name, email, phone
     * @param string $callbackUrl where the customer returns after paying (our /admin/payments page)
     * @return array{checkout_url:string,gateway_reference:string}
     */
    public function createCheckout(int $amountCentavos, string $invoiceNumber, array $customer, string $callbackUrl): array
    {
        $payload = array_filter([
            'amount'           => number_format($amountCentavos / 100, 2, '.', ''),
            'currency'         => $this->currency,
            'reference_number' => $invoiceNumber,
            'email'            => $customer['email'] ?? null,
            'name'             => $customer['name']  ?? null,
            'phone'            => $customer['phone'] ?? null,
            'redirect_url'     => $callbackUrl,
            'webhook'          => (string) config('payment.gateways.hitpay.webhook_url'),
            'allow_repeated_payments' => 'false',
        ], fn ($v) => $v !== null && $v !== '');

        $response = Http::withHeaders([
            'X-BUSINESS-API-KEY' => $this->apiKey,
            'X-Requested-With'   => 'XMLHttpRequest',
        ])->asForm()->timeout(15)->post($this->baseUrl() . '/payment-requests', $payload);

        if (! $response->successful()) {
            Log::error('HitpayGateway::createCheckout failed', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 500),
            ]);
            throw new RuntimeException('HitPay createCheckout failed: HTTP ' . $response->status());
        }

        $data = $response->json() ?? [];
        return [
            'checkout_url'      => (string) ($data['url'] ?? ''),
            'gateway_reference' => (string) ($data['id']  ?? ''),
        ];
    }

    /**
     * Validate + normalize an incoming webhook callback.
     * Returns ['gateway_reference', 'status', 'amount_centavos', 'reference_number']
     * or null if signature invalid / payload incomplete.
     */
    public function handleWebhook(array $payload): ?array
    {
        $receivedHmac = $payload['hmac'] ?? null;
        if (! $receivedHmac) {
            Log::warning('HitpayGateway: webhook missing hmac');
            return null;
        }

        if (! $this->verifyHmac($payload, $receivedHmac)) {
            Log::warning('HitpayGateway: webhook hmac mismatch');
            return null;
        }

        return [
            'gateway_reference' => (string) ($payload['payment_request_id'] ?? $payload['payment_id'] ?? ''),
            'payment_id'        => (string) ($payload['payment_id'] ?? ''),
            'status'            => $this->mapStatus((string) ($payload['status'] ?? '')),
            'amount_centavos'   => (int) round(((float) ($payload['amount'] ?? 0)) * 100),
            'reference_number'  => (string) ($payload['reference_number'] ?? ''),
            'currency'          => (string) ($payload['currency'] ?? ''),
        ];
    }

    private function verifyHmac(array $payload, string $received): bool
    {
        $toSign = $payload;
        unset($toSign['hmac']);
        ksort($toSign);
        $data = '';
        foreach ($toSign as $key => $value) {
            $data .= $key . $value;
        }
        $expected = hash_hmac('sha256', $data, $this->salt);
        return hash_equals($expected, $received);
    }

    private function mapStatus(string $hitpayStatus): string
    {
        return match (strtolower($hitpayStatus)) {
            'completed', 'succeeded' => 'completed',
            'pending'                => 'pending',
            'failed', 'cancelled'    => 'failed',
            'refunded'               => 'refunded',
            default                  => 'pending',
        };
    }

    public function refund(string $gatewayReference, int $amountCentavos): bool
    {
        try {
            $r = Http::withHeaders([
                'X-BUSINESS-API-KEY' => $this->apiKey,
            ])->asForm()->timeout(15)->post($this->baseUrl() . '/refund', [
                'payment_id' => $gatewayReference,
                'amount'     => number_format($amountCentavos / 100, 2, '.', ''),
            ]);
            return $r->successful();
        } catch (Throwable $e) {
            Log::error('HitpayGateway::refund threw', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
