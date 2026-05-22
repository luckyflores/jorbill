<?php

namespace App\Services\Odoo\Contracts;

interface OdooClient
{
    public function id(): string;

    public function testConnection(): array;

    public function findOrCreatePartner(array $customer): ?int;

    public function getPartner(int $id): ?array;

    public function listPartners(int $limit = 50, int $offset = 0): array;

    /**
     * Create + post a customer invoice (account.move out_invoice).
     * @param array $invoice keyed: invoice_number, issued_at (Y-m-d), due_at, ...
     * @param array $lineItems each keyed: description, quantity, unit_price_centavos
     * @return int|null Odoo account.move id, or null on failure
     */
    public function pushInvoice(array $invoice, array $lineItems, int $partnerId): ?int;

    /**
     * Create + post an account.payment.
     * @param array $payment keyed: payment_number, amount_centavos, received_at, gateway
     * @return int|null Odoo account.payment id
     */
    public function pushPayment(array $payment, int $partnerId): ?int;

    /** Cancel a posted Odoo payment (creates reversal in the journal). */
    public function cancelPayment(int $odooPaymentId): bool;
}
