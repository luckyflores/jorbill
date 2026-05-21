<?php

namespace Tests\Unit;

use App\Services\Network\Contracts\GenieAcsClient;
use App\Services\Network\Contracts\MikrotikClient;
use App\Services\Network\Contracts\UispClient;
use App\Services\Network\Null\NullGenieAcsClient;
use App\Services\Network\Null\NullMikrotikClient;
use App\Services\Network\Null\NullUispClient;
use Tests\TestCase;

class NetworkBindingsTest extends TestCase
{
    public function test_uisp_client_resolves_to_null_implementation_by_default(): void
    {
        $this->assertInstanceOf(NullUispClient::class, app(UispClient::class));
    }

    public function test_genieacs_client_resolves_to_null_implementation_by_default(): void
    {
        $this->assertInstanceOf(NullGenieAcsClient::class, app(GenieAcsClient::class));
    }

    public function test_mikrotik_client_resolves_to_null_implementation_by_default(): void
    {
        $this->assertInstanceOf(NullMikrotikClient::class, app(MikrotikClient::class));
    }
}
