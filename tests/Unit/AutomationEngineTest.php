<?php

namespace Tests\Unit;

use App\Models\AutomationRule;
use App\Models\AutomationRuleExecution;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\Automation\AutomationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutomationEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_rule_fires_when_event_matches_and_conditions_match(): void
    {
        $rule = AutomationRule::create([
            'name' => 'test', 'is_enabled' => true, 'trigger_type' => 'model',
            'trigger_config' => ['model' => Subscription::class, 'when' => 'updated', 'if_changed' => 'status'],
            'conditions' => [['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'active']],
            'actions' => [['type' => 'log_activity', 'description' => 'fired for {{subscription.id}}']],
        ]);

        $customer = Customer::create([
            'name' => 'Test', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $service = Service::create([
            'name' => 'Svc', 'slug' => 'svc', 'code' => 'SVC', 'type' => 'pppoe',
            'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000, 'price_centavos' => 99900,
        ]);
        $sub = Subscription::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'status' => 'pending', 'username' => 'u',
        ]);

        // change status — should fire the rule
        $sub->status = 'active';
        $sub->save();

        $execs = AutomationRuleExecution::where('rule_id', $rule->id)->get();
        $this->assertCount(1, $execs);
        $this->assertTrue($execs->first()->conditions_matched);
    }

    public function test_rule_does_not_fire_when_if_changed_field_is_unchanged(): void
    {
        $rule = AutomationRule::create([
            'name' => 'test', 'is_enabled' => true, 'trigger_type' => 'model',
            'trigger_config' => ['model' => Subscription::class, 'when' => 'updated', 'if_changed' => 'status'],
            'conditions' => [],
            'actions' => [['type' => 'log_activity', 'description' => 'fired']],
        ]);

        $customer = Customer::create([
            'name' => 'Test', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $service = Service::create([
            'name' => 'Svc', 'slug' => 'svc2', 'code' => 'SVC2', 'type' => 'pppoe',
            'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000, 'price_centavos' => 99900,
        ]);
        $sub = Subscription::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'status' => 'pending', 'username' => 'u2',
        ]);

        // change a different field — should NOT fire
        $sub->notes = 'something';
        $sub->save();

        $this->assertSame(0, AutomationRuleExecution::where('rule_id', $rule->id)->count());
    }

    public function test_rule_skips_when_conditions_do_not_match(): void
    {
        $rule = AutomationRule::create([
            'name' => 'test', 'is_enabled' => true, 'trigger_type' => 'model',
            'trigger_config' => ['model' => Subscription::class, 'when' => 'updated', 'if_changed' => 'status'],
            'conditions' => [['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'active']],
            'actions' => [['type' => 'log_activity', 'description' => 'fired']],
        ]);

        $customer = Customer::create([
            'name' => 'Test', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $service = Service::create([
            'name' => 'Svc', 'slug' => 'svc3', 'code' => 'SVC3', 'type' => 'pppoe',
            'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000, 'price_centavos' => 99900,
        ]);
        $sub = Subscription::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'status' => 'pending', 'username' => 'u3',
        ]);

        // change status — but to 'suspended', not 'active' (which is what the rule wants)
        $sub->status = 'suspended';
        $sub->save();

        $execs = AutomationRuleExecution::where('rule_id', $rule->id)->get();
        $this->assertCount(1, $execs);
        $this->assertFalse($execs->first()->conditions_matched);
    }

    public function test_disabled_rule_does_not_fire(): void
    {
        $rule = AutomationRule::create([
            'name' => 'test', 'is_enabled' => false, 'trigger_type' => 'model',
            'trigger_config' => ['model' => Subscription::class, 'when' => 'updated'],
            'conditions' => [],
            'actions' => [['type' => 'log_activity', 'description' => 'fired']],
        ]);

        $customer = Customer::create([
            'name' => 'Test', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $service = Service::create([
            'name' => 'Svc', 'slug' => 'svc4', 'code' => 'SVC4', 'type' => 'pppoe',
            'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000, 'price_centavos' => 99900,
        ]);
        $sub = Subscription::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'status' => 'pending', 'username' => 'u4',
        ]);
        $sub->status = 'active';
        $sub->save();

        $this->assertSame(0, AutomationRuleExecution::where('rule_id', $rule->id)->count());
    }
}
