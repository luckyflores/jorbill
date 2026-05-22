<?php

namespace App\Services\Odoo\Contracts;

interface OdooClient
{
    public function id(): string;

    public function testConnection(): array;   // returns ['ok'=>bool, 'uid'=>?int, 'server_version'=>?string, 'error'=>?string]

    /** Find existing partner by external ref (JorBill customer_code) or by name+phone. Returns Odoo res.partner id (creates if missing). */
    public function findOrCreatePartner(array $customer): ?int;

    /** Read full partner record by id. */
    public function getPartner(int $id): ?array;

    /** List partners (paginated). */
    public function listPartners(int $limit = 50, int $offset = 0): array;
}
