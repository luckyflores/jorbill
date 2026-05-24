<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticsApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeTech(): User
    {
        return User::create(['name' => 'T', 'email' => 't@x.com', 'password' => 'secret123', 'role' => 'tech']);
    }

    private function makeCustomer(array $overrides = []): Customer
    {
        return Customer::create(array_merge([
            'name' => 'C', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
            'email' => 'c@x.com', 'password' => 'secret123', 'portal_enabled' => true, 'status' => 'active',
        ], $overrides));
    }

    public function test_tech_can_get_token_then_search_customers(): void
    {
        $this->makeTech();
        $this->makeCustomer(['name' => 'John Cruz', 'customer_code' => 'C-00099']);

        $token = $this->postJson('/api/auth/token', [
            'email' => 't@x.com', 'password' => 'secret123', 'actor' => 'tech',
        ])->assertOk()->json('token');

        $r = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/customers/search?q=John')->assertOk()->json();

        $this->assertCount(1, $r['data']);
        $this->assertSame('John Cruz', $r['data'][0]['name']);
    }

    public function test_customer_cannot_search_customers(): void
    {
        $this->makeCustomer();
        $token = $this->postJson('/api/auth/token', [
            'email' => 'c@x.com', 'password' => 'secret123', 'actor' => 'customer',
        ])->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/customers/search?q=x')->assertStatus(403);
    }

    public function test_tech_posts_diagnostic_for_a_customer(): void
    {
        $tech = $this->makeTech();
        $cust = $this->makeCustomer();
        $token = $this->postJson('/api/auth/token', [
            'email' => 't@x.com', 'password' => 'secret123', 'actor' => 'tech',
        ])->json('token');

        $r = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/diagnostics', [
                'customer_id'  => $cust->id,
                'wifi'         => ['ssid' => 'FourLeaf-Home', 'rssi' => -65, 'bssid' => 'aa:bb:cc'],
                'ping_results' => [['target' => '8.8.8.8', 'avg_ms' => 12.4, 'loss_pct' => 0]],
                'speedtest'    => ['download_mbps' => 47.5, 'upload_mbps' => 22.1],
                'notes'        => 'Signal weak in master bedroom',
                'app_version'  => '0.1.0',
                'device_info'  => ['model' => 'Pixel 6', 'os' => 'Android 14'],
            ])->assertStatus(201)->json();

        $this->assertSame($cust->id, $r['data']['customer_id']);
        $this->assertSame($tech->id, $r['data']['tech_user_id']);
        $this->assertSame(-65, $r['data']['wifi']['rssi']);
    }

    public function test_customer_post_attributes_to_self(): void
    {
        $cust = $this->makeCustomer();
        $token = $this->postJson('/api/auth/token', [
            'email' => 'c@x.com', 'password' => 'secret123', 'actor' => 'customer',
        ])->json('token');

        $r = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/diagnostics', ['wifi' => ['rssi' => -70]])
            ->assertStatus(201)->json();

        $this->assertSame($cust->id, $r['data']['customer_id']);
        $this->assertNull($r['data']['tech_user_id']);
    }

    public function test_login_rejects_bad_password(): void
    {
        $this->makeTech();
        $this->postJson('/api/auth/token', [
            'email' => 't@x.com', 'password' => 'wrong', 'actor' => 'tech',
        ])->assertStatus(401);
    }

    public function test_config_endpoint_returns_defaults(): void
    {
        Setting::put('diagnostics.fourleaf_gateway', '10.10.0.1');
        Setting::put('diagnostics.ping_targets', 'google.com, 1.1.1.1');

        $r = $this->getJson('/api/config/diagnostics')->assertOk()->json();
        $this->assertSame('10.10.0.1', $r['fourleaf_gateway']);
        $this->assertContains('google.com', $r['ping_targets']);
        $this->assertContains('1.1.1.1', $r['ping_targets']);
    }
}
