<?php

namespace Tests\Unit;

use App\Models\Olt;
use App\Services\Network\Contracts\OltClient;
use App\Services\Network\Contracts\OltClientFactory;
use App\Services\Network\Null\NullOltClient;
use App\Services\Network\Null\NullOltClientFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OltFactoryBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_olt_factory_resolves_to_null_by_default(): void
    {
        $this->assertInstanceOf(NullOltClientFactory::class, app(OltClientFactory::class));
    }

    public function test_null_factory_returns_null_client_for_any_olt(): void
    {
        $olt = Olt::create([
            'name' => 'test', 'vendor' => 'zte_cli', 'ip_address' => '10.0.0.1',
            'ssh_user' => 'admin', 'ssh_password' => 'x',
        ]);
        $client = app(OltClientFactory::class)->forOlt($olt);
        $this->assertInstanceOf(NullOltClient::class, $client);
        $this->assertTrue($client->connect());
    }
}
