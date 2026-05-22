<?php

namespace Tests\Unit;

use App\Services\Odoo\Contracts\OdooClient;
use App\Services\Odoo\Null\NullOdooClient;
use Tests\TestCase;

class OdooClientBindingTest extends TestCase
{
    public function test_resolves_to_null_by_default(): void
    {
        $this->assertInstanceOf(NullOdooClient::class, app(OdooClient::class));
    }

    public function test_null_client_test_connection_returns_ok(): void
    {
        $r = app(OdooClient::class)->testConnection();
        $this->assertTrue($r['ok']);
        $this->assertSame('null-driver', $r['server_version']);
    }

    public function test_null_client_find_or_create_returns_zero(): void
    {
        $this->assertSame(0, app(OdooClient::class)->findOrCreatePartner(['name' => 'Test']));
    }
}
