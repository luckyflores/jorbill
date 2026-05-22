<?php

namespace Tests\Unit;

use App\Models\AutomationRule;
use App\Models\AutomationRuleExecution;
use App\Models\Customer;
use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\Automation\AutomationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendTemplateActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_template_interpolates_and_logs_per_channel(): void
    {
        NotificationTemplate::create([
            'name' => 'welcome', 'label' => 'Welcome',
            'channel' => 'sms',
            'body' => 'Hi {{customer.name}}, sub {{subscription.username}} is active.',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Jane', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $service = Service::create([
            'name' => 'F50', 'slug' => 'f50', 'code' => 'F50', 'type' => 'pppoe',
            'bandwidth_down_kbps' => 50000, 'bandwidth_up_kbps' => 25000, 'price_centavos' => 99900,
        ]);
        $sub = Subscription::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'status' => 'pending', 'username' => 'jane99', 'password' => 'p',
        ]);

        $rule = AutomationRule::create([
            'name' => 't', 'is_enabled' => true, 'trigger_type' => 'model',
            'trigger_config' => ['model' => Subscription::class, 'when' => 'updated', 'if_changed' => 'status'],
            'conditions' => [['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'active']],
            'actions' => [[
                'type' => 'send_template',
                'template' => 'welcome',
                'channels' => ['log', 'null'],
                'to' => '{{customer.phone}}',
            ]],
        ]);

        // trigger
        $sub->status = 'active';
        $sub->save();

        // both channels should have produced a NotificationLog row
        $logs = NotificationLog::where('event', 'template:welcome')->get();
        $this->assertCount(2, $logs);
        $this->assertSame('Hi Jane, sub jane99 is active.', $logs->first()->body);
        $this->assertSame(['log', 'null'], $logs->pluck('driver')->sort()->values()->all());

        // template use_count was bumped
        $this->assertSame(1, NotificationTemplate::where('name', 'welcome')->value('use_count'));
    }

    public function test_send_template_missing_template_returns_failure(): void
    {
        $customer = Customer::create([
            'name' => 'X', 'phone' => '09', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        $service = Service::create([
            'name' => 'S', 'slug' => 's-mt', 'code' => 'SMT', 'type' => 'pppoe',
            'bandwidth_down_kbps' => 1, 'bandwidth_up_kbps' => 1, 'price_centavos' => 1,
        ]);
        $sub = Subscription::create([
            'customer_id' => $customer->id, 'service_id' => $service->id,
            'status' => 'pending', 'username' => 'mt',
        ]);

        $rule = AutomationRule::create([
            'name' => 'mt', 'is_enabled' => true, 'trigger_type' => 'model',
            'trigger_config' => ['model' => Subscription::class, 'when' => 'updated', 'if_changed' => 'status'],
            'actions' => [['type' => 'send_template', 'template' => 'no_such_template', 'channels' => ['log']]],
        ]);

        $sub->status = 'active';
        $sub->save();

        $exec = AutomationRuleExecution::where('rule_id', $rule->id)->first();
        $this->assertNotNull($exec);
        $this->assertSame(false, $exec->actions_executed[0]['ok']);
        $this->assertStringContainsString('not found', $exec->actions_executed[0]['detail']);
    }
}
