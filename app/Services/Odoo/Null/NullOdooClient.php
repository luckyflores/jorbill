<?php

namespace App\Services\Odoo\Null;

use App\Services\Odoo\Contracts\OdooClient;
use Illuminate\Support\Facades\Log;

class NullOdooClient implements OdooClient
{
    public function id(): string { return 'null'; }

    public function testConnection(): array
    {
        Log::info('NullOdooClient::testConnection');
        return ['ok' => true, 'uid' => 0, 'server_version' => 'null-driver', 'error' => null];
    }

    public function findOrCreatePartner(array $customer): ?int
    {
        Log::info('NullOdooClient::findOrCreatePartner', ['name' => $customer['name'] ?? null]);
        return 0;
    }

    public function getPartner(int $id): ?array
    {
        Log::info('NullOdooClient::getPartner', compact('id'));
        return null;
    }

    public function listPartners(int $limit = 50, int $offset = 0): array
    {
        Log::info('NullOdooClient::listPartners');
        return [];
    }

    public function pushInvoice(array $invoice, array $lineItems, int $partnerId): ?int
    {
        Log::info('NullOdooClient::pushInvoice', ['ref' => $invoice['invoice_number'] ?? null, 'lines' => count($lineItems), 'partner' => $partnerId]);
        return 0;
    }

    public function pushPayment(array $payment, int $partnerId): ?int
    {
        Log::info('NullOdooClient::pushPayment', ['ref' => $payment['payment_number'] ?? null, 'amount' => $payment['amount_centavos'] ?? null, 'partner' => $partnerId]);
        return 0;
    }

    public function cancelPayment(int $odooPaymentId): bool
    {
        Log::info('NullOdooClient::cancelPayment', compact('odooPaymentId'));
        return true;
    }

}
