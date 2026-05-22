<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use Illuminate\Database\Seeder;

class AutomationScheduledRuleSeeder extends Seeder
{
    public function run(): void
    {
        AutomationRule::query()->where('name', 'like', '[Example-Scheduled]%')->delete();

        AutomationRule::create([
            'name' => '[Example-Scheduled] Cut overdue invoices on day 5 at 08:00',
            'description' => 'On the 5th of each month at 08:00, find all overdue invoices that are more than 5 days past due, suspend the linked subscription, and SMS the customer.',
            'is_enabled' => false,
            'trigger_type' => 'scheduled',
            'trigger_config' => [
                'schedule_type' => 'monthly',
                'day_of_month'  => 5,
                'time'          => '08:00',
                'target_model'  => 'App\\Models\\Invoice',
            ],
            'target_filter' => [
                ['field' => 'status', 'operator' => 'eq', 'value' => 'overdue'],
                ['field' => 'days_overdue', 'operator' => 'gt', 'value' => '5'],
            ],
            'conditions' => [],
            'actions' => [
                [
                    'type'   => 'update_field',
                    'target' => 'subscription.status',
                    'value'  => 'suspended',
                ],
                [
                    'type' => 'send_sms',
                    'to'   => '{{customer.phone}}',
                    'body' => 'Hi {{customer.name}}, your service has been suspended for invoice {{invoice.invoice_number}} ({{invoice.days_overdue}} days overdue). Please pay to restore.',
                ],
                [
                    'type' => 'log_activity',
                    'description' => 'Auto-suspended for overdue invoice {{invoice.invoice_number}}',
                ],
            ],
        ]);

        AutomationRule::create([
            'name' => '[Example-Scheduled] Daily payment reminder at 09:00',
            'description' => 'Daily 09:00 — for invoices due in the next 3 days, send a friendly reminder SMS.',
            'is_enabled' => false,
            'trigger_type' => 'scheduled',
            'trigger_config' => [
                'schedule_type' => 'daily',
                'time'          => '09:00',
                'target_model'  => 'App\\Models\\Invoice',
            ],
            'target_filter' => [
                ['field' => 'status', 'operator' => 'in', 'value' => 'issued'],
                ['field' => 'days_until_due', 'operator' => 'lt', 'value' => '4'],
                ['field' => 'days_until_due', 'operator' => 'gt', 'value' => '0'],
            ],
            'conditions' => [],
            'actions' => [
                [
                    'type' => 'send_sms',
                    'to'   => '{{customer.phone}}',
                    'body' => 'Hi {{customer.name}}, your invoice {{invoice.invoice_number}} is due in {{invoice.days_until_due}} day(s). Total: ₱{{invoice.total_centavos}}.',
                ],
            ],
        ]);

        $this->command->info('  ✓ 2 example scheduled rules seeded (disabled).');
    }
}
