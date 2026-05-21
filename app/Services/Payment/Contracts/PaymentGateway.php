<?php

namespace App\Services\Payment\Contracts;

interface PaymentGateway
{
    public function id(): string;

    /** @return array<int, string> */
    public function supportedMethods(): array;

    /**
     * Create a checkout/payment intent at the gateway.
     * @return array{checkout_url: string, gateway_reference: string}
     */
    public function createCheckout(
        int $amountCentavos,
        string $invoiceNumber,
        array $customer,
        string $callbackUrl
    ): array;

    /**
     * Normalize a webhook payload from the gateway.
     * @return array{gateway_reference: string, status: string, amount_centavos: int}|null
     */
    public function handleWebhook(array $payload): ?array;

    public function refund(string $gatewayReference, int $amountCentavos): bool;
}
