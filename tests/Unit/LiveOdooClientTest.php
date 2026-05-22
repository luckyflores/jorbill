<?php

namespace Tests\Unit;

use App\Services\Odoo\Live\LiveOdooClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LiveOdooClientTest extends TestCase
{
    public function test_authenticate_then_find_or_create_partner_existing(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response([
                'result' => ['uid' => 2, 'session_id' => 'sess-abc'],
            ], 200, ['Set-Cookie' => 'session_id=sess-abc; Path=/']),

            '*/web/dataset/call_kw' => Http::sequence()
                ->push(['result' => [['id' => 42]]], 200)       // search_read returns existing partner id=42
                ,
        ]);

        $client = new LiveOdooClient(
            baseUrl: 'http://odoo.local',
            db: 'db1',
            login: 'admin',
            password: 'secret',
        );
        $id = $client->findOrCreatePartner(['customer_code' => 'C-00001', 'name' => 'John', 'phone' => '0917']);
        $this->assertSame(42, $id);
    }

    public function test_find_or_create_creates_when_missing(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => ['uid' => 2]], 200),
            '*/web/dataset/call_kw' => Http::sequence()
                ->push(['result' => []], 200)        // search_read empty → create path
                ->push(['result' => 99], 200)         // create returns new id
                ,
        ]);

        $client = new LiveOdooClient(
            baseUrl: 'http://odoo.local',
            db: 'db1', login: 'a', password: 'b',
        );
        $id = $client->findOrCreatePartner(['customer_code' => 'C-99999', 'name' => 'Jane', 'phone' => '0918']);
        $this->assertSame(99, $id);
    }

    public function test_test_connection_reports_failure_on_bad_creds(): void
    {
        Http::fake([
            '*/web/session/authenticate' => Http::response(['result' => false], 200),
        ]);

        $client = new LiveOdooClient(
            baseUrl: 'http://odoo.local',
            db: 'db1', login: 'wrong', password: 'wrong',
        );
        $r = $client->testConnection();
        $this->assertFalse($r['ok']);
        $this->assertSame('authentication failed', $r['error']);
    }
}
