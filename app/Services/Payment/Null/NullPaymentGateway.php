<?php

namespace App\Services\Payment\Null;

use App\Services\Payment\Contracts\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NullPaymentGateway implements PaymentGateway
{
    public function id(): string
    {
        return 'null';
    }

    public function supportedMethods(): array
    {
        return ['gcash', 'card', 'bank_transfer'];
    }

    public function createCheckout(int $amountCentavos, string $invoiceNumber, array $customer, string $callbackUrl): array
    {
        $ref = 'NULL-' . Str::random(12);
        Log::info('NullPaymentGateway::createCheckout', compact('amountCentavos', 'invoiceNumber', 'ref'));
        return [
            'checkout_url' => $callbackUrl . '?gateway_reference=' . $ref . '&status=completed',
            'gateway_reference' => $ref,
        ];
    }

    public function handleWebhook(array $payload): ?array
    {
        Log::info('NullPaymentGateway::handleWebhook', $payload);
        return [
            'gateway_reference' => (string) ($payload['gateway_reference'] ?? ''),
            'status' => (string) ($payload['status'] ?? 'completed'),
            'amount_centavos' => (int) ($payload['amount_centavos'] ?? 0),
        ];
    }

    public function refund(string $gatewayReference, int $amountCentavos): bool
    {
        Log::info('NullPaymentGateway::refund', compact('gatewayReference', 'amountCentavos'));
        return true;
    }
}
