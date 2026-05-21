<?php

namespace Tests\Unit;

use App\Models\Router;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\Subscriptions\SubscriptionProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionProvisionerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_returns_false_when_no_router_assigned(): void
    {
        $sub = new Subscription([
            'customer_id' => 1, 'service_id' => 1, 'status' => 'active',
        ]);
        $sub->id = 1;
        $this->assertFalse(app(SubscriptionProvisioner::class)->sync($sub));
    }

    public function test_sync_returns_false_when_router_inactive(): void
    {
        $router = Router::create([
            'name' => 'r1', 'ip_address' => '10.0.0.1', 'api_user' => 'admin', 'api_password' => 'x',
            'is_active' => false,
        ]);
        $svc = Service::create([
            'name' => 'Fiber 50', 'slug' => 'fiber-50', 'code' => 'F50',
            'type' => 'pppoe', 'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000,
            'price_centavos' => 99900,
        ]);
        $sub = Subscription::create([
            'customer_id' => 1, 'service_id' => $svc->id, 'router_id' => $router->id,
            'status' => 'active', 'username' => 'u1', 'password' => 'p',
        ]);
        $this->assertFalse(app(SubscriptionProvisioner::class)->sync($sub));
    }

    public function test_sync_pppoe_active_returns_true_with_null_driver(): void
    {
        $router = Router::create([
            'name' => 'r1', 'ip_address' => '10.0.0.1', 'api_user' => 'admin', 'api_password' => 'x',
            'is_active' => true,
        ]);
        $svc = Service::create([
            'name' => 'Fiber 50', 'slug' => 'fiber-50-x', 'code' => 'F50X',
            'type' => 'pppoe', 'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000,
            'price_centavos' => 99900, 'mikrotik_profile_name' => 'fiber-50',
        ]);
        $sub = Subscription::create([
            'customer_id' => 1, 'service_id' => $svc->id, 'router_id' => $router->id,
            'status' => 'active', 'username' => 'u1', 'password' => 'p',
        ]);
        // NullMikrotikClient returns true for everything ? provisioner should return true
        $this->assertTrue(app(SubscriptionProvisioner::class)->sync($sub));
    }
}
