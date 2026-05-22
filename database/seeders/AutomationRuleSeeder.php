<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use Illuminate\Database\Seeder;

class AutomationRuleSeeder extends Seeder
{
    public function run(): void
    {
        AutomationRule::query()->where('name', 'like', '[Example]%')->delete();

        AutomationRule::create([
            'name' => '[Example] Welcome SMS on subscription activation',
            'description' => 'When a Subscription is updated and its status changes to active (from pending), send a welcome SMS.',
            'is_enabled' => false,
            'trigger_type' => 'model',
            'trigger_config' => [
                'model' => 'App\\Models\\Subscription',
                'when' => 'updated',
                'if_changed' => 'status',
            ],
            'conditions' => [
                ['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'active'],
            ],
            'actions' => [
                [
                    'type' => 'send_sms',
                    'to' => '{{customer.phone}}',
                    'body' => 'Hi {{customer.name}}, your service is now ACTIVE. Username: {{subscription.username}}. Welcome!',
                ],
                [
                    'type' => 'log_activity',
                    'description' => 'Welcome SMS sent for subscription {{subscription.id}}',
                ],
            ],
        ]);

        AutomationRule::create([
            'name' => '[Example] Notify on suspension',
            'description' => 'When a Subscription is updated and status changes to suspended, SMS the customer with the reason.',
            'is_enabled' => false,
            'trigger_type' => 'model',
            'trigger_config' => [
                'model' => 'App\\Models\\Subscription',
                'when' => 'updated',
                'if_changed' => 'status',
            ],
            'conditions' => [
                ['field' => 'subscription.status', 'operator' => 'eq', 'value' => 'suspended'],
            ],
            'actions' => [
                [
                    'type' => 'send_sms',
                    'to' => '{{customer.phone}}',
                    'body' => 'Hi {{customer.name}}, your internet has been suspended. Please settle your account to restore service.',
                ],
            ],
        ]);

        AutomationRule::create([
            'name' => '[Example] Create JO on ONU faulty',
            'description' => 'When an ONU is updated and its status changes to faulty, create a high-priority Job Order.',
            'is_enabled' => false,
            'trigger_type' => 'model',
            'trigger_config' => [
                'model' => 'App\\Models\\Onu',
                'when' => 'updated',
                'if_changed' => 'status',
            ],
            'conditions' => [
                ['field' => 'onu.status', 'operator' => 'eq', 'value' => 'faulty'],
            ],
            'actions' => [
                [
                    'type' => 'create_job_order',
                    'job_type' => 'repair',
                    'priority' => 'high',
                    'description' => 'ONU {{onu.serial_number}} marked faulty — investigate at NAP {{onu.nap_id}}',
                ],
            ],
        ]);

        $this->command->info('  ✓ 3 example rules seeded (disabled). Enable them via the panel to study how rules behave.');
    }
}
