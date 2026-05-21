<?php

namespace Tests\Unit;

use App\Services\Network\Contracts\MikrotikClient;
use App\Services\Network\Contracts\MikrotikClientFactory;
use App\Services\Network\Null\NullMikrotikClient;
use App\Services\Network\Null\NullMikrotikClientFactory;
use Tests\TestCase;

class MikrotikFactoryBindingTest extends TestCase
{
    public function test_mikrotik_factory_resolves_to_null_by_default(): void
    {
        $this->assertInstanceOf(NullMikrotikClientFactory::class, app(MikrotikClientFactory::class));
    }

    public function test_legacy_mikrotik_client_still_resolves_to_null(): void
    {
        $this->assertInstanceOf(NullMikrotikClient::class, app(MikrotikClient::class));
    }
}
