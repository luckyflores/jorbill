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
}
