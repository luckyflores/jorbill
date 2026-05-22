<?php

namespace Tests\Unit;

use App\Models\AutomationRule;
use App\Models\AutomationRuleExecution;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Automation\AutomationEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ScheduledAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_cron_for_each_schedule_type(): void
    {
        $engine = app(AutomationEngine::class);

        $this->assertSame('0 8 * * *',  $engine->buildCronExpression(['schedule_type' => 'daily',   'time' => '08:00']));
        $this->assertSame('30 9 * * 1', $engine->buildCronExpression(['schedule_type' => 'weekly',  'time' => '09:30', 'day_of_week' => 1]));
        $this->assertSame('0 8 5 * *',  $engine->buildCronExpression(['schedule_type' => 'monthly', 'time' => '08:00', 'day_of_month' => 5]));
        $this->assertSame('*/15 * * * *', $engine->buildCronExpression(['schedule_type' => 'cron', 'cron' => '*/15 * * * *']));
    }

    public function test_is_due_matches_only_at_the_scheduled_minute(): void
    {
        $engine = app(AutomationEngine::class);
        $rule = AutomationRule::create([
            'name' => 'test', 'is_enabled' => true, 'trigger_type' => 'scheduled',
            'trigger_config' => ['schedule_type' => 'daily', 'time' => '08:00', 'target_model' => Customer::class],
            'actions' => [['type' => 'log_activity', 'description' => 'fired']],
        ]);

        // exactly 08:00 on any day → due
        $this->assertTrue($engine->isScheduledRuleDue($rule, Carbon::create(2026, 6, 15, 8, 0)));
        // 08:01 → not due
        $this->assertFalse($engine->isScheduledRuleDue($rule, Carbon::create(2026, 6, 15, 8, 1)));
        // 07:00 → not due
        $this->assertFalse($engine->isScheduledRuleDue($rule, Carbon::create(2026, 6, 15, 7, 0)));
    }

    public function test_monthly_day_of_month_matching(): void
    {
        $engine = app(AutomationEngine::class);
        $rule = AutomationRule::create([
            'name' => 'cut5', 'is_enabled' => true, 'trigger_type' => 'scheduled',
            'trigger_config' => ['schedule_type' => 'monthly', 'day_of_month' => 5, 'time' => '08:00', 'target_model' => Invoice::class],
            'actions' => [['type' => 'log_activity', 'description' => 'cut']],
        ]);

        $this->assertTrue($engine->isScheduledRuleDue($rule,  Carbon::create(2026, 6,  5, 8, 0)));
        $this->assertFalse($engine->isScheduledRuleDue($rule, Carbon::create(2026, 6,  6, 8, 0)));
        $this->assertFalse($engine->isScheduledRuleDue($rule, Carbon::create(2026, 6,  5, 8, 1)));
    }

    public function test_scheduled_rule_processes_matching_records(): void
    {
        $customer = Customer::create([
            'name' => 'A', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);

        // 2 overdue (>5 days), 1 not overdue, 1 paid — only the 2 overdue should be processed
        Invoice::create([
            'invoice_number' => 'I-1', 'series_code' => 'SI', 'customer_id' => $customer->id,
            'issued_at' => now()->subDays(30), 'due_at' => now()->subDays(10),
            'subtotal_centavos' => 100, 'vat_centavos' => 12, 'total_centavos' => 112,
            'status' => 'overdue', 'amount_paid_centavos' => 0,
        ]);
        Invoice::create([
            'invoice_number' => 'I-2', 'series_code' => 'SI', 'customer_id' => $customer->id,
            'issued_at' => now()->subDays(30), 'due_at' => now()->subDays(8),
            'subtotal_centavos' => 100, 'vat_centavos' => 12, 'total_centavos' => 112,
            'status' => 'overdue', 'amount_paid_centavos' => 0,
        ]);
        Invoice::create([
            'invoice_number' => 'I-3', 'series_code' => 'SI', 'customer_id' => $customer->id,
            'issued_at' => now()->subDays(20), 'due_at' => now()->subDays(2),     // overdue but only 2 days
            'subtotal_centavos' => 100, 'vat_centavos' => 12, 'total_centavos' => 112,
            'status' => 'overdue', 'amount_paid_centavos' => 0,
        ]);
        Invoice::create([
            'invoice_number' => 'I-4', 'series_code' => 'SI', 'customer_id' => $customer->id,
            'issued_at' => now()->subDays(30), 'due_at' => now()->subDays(10),
            'subtotal_centavos' => 100, 'vat_centavos' => 12, 'total_centavos' => 112,
            'status' => 'paid', 'amount_paid_centavos' => 112,                    // paid — skip
        ]);

        $rule = AutomationRule::create([
            'name' => 'cut', 'is_enabled' => true, 'trigger_type' => 'scheduled',
            'trigger_config' => ['schedule_type' => 'monthly', 'day_of_month' => 5, 'time' => '08:00', 'target_model' => Invoice::class],
            'target_filter' => [
                ['field' => 'status', 'operator' => 'eq', 'value' => 'overdue'],
                ['field' => 'days_overdue', 'operator' => 'gt', 'value' => '5'],
            ],
            'actions' => [['type' => 'log_activity', 'description' => 'overdue invoice {{invoice.invoice_number}}']],
        ]);

        $count = app(AutomationEngine::class)->fireScheduledRule($rule);
        $this->assertSame(2, $count);
        $this->assertSame(2, AutomationRuleExecution::where('rule_id', $rule->id)->count());
    }

    public function test_dry_run_does_not_change_records_or_log_fire(): void
    {
        $customer = Customer::create([
            'name' => 'A', 'phone' => '0917', 'address_line1' => 'a', 'city' => 'b', 'province' => 'c',
        ]);
        Invoice::create([
            'invoice_number' => 'I-DR', 'series_code' => 'SI', 'customer_id' => $customer->id,
            'issued_at' => now()->subDays(30), 'due_at' => now()->subDays(20),
            'subtotal_centavos' => 100, 'vat_centavos' => 12, 'total_centavos' => 112,
            'status' => 'overdue', 'amount_paid_centavos' => 0,
        ]);

        $rule = AutomationRule::create([
            'name' => 'dr', 'is_enabled' => true, 'trigger_type' => 'scheduled',
            'trigger_config' => ['schedule_type' => 'daily', 'time' => '00:00', 'target_model' => Invoice::class],
            'target_filter' => [['field' => 'status', 'operator' => 'eq', 'value' => 'overdue']],
            'actions' => [['type' => 'log_activity', 'description' => 'fired']],
        ]);

        $countBefore = (int) ($rule->fire_count ?? 0);
        app(AutomationEngine::class)->fireScheduledRule($rule, dryRun: true);
        $rule->refresh();
        $this->assertSame($countBefore, (int) $rule->fire_count);  // dry-run does NOT bump fire_count
        // execution log row IS created (so user can review what would happen)
        $this->assertSame(1, AutomationRuleExecution::where('rule_id', $rule->id)->count());
    }

    public function test_multi_day_of_month_with_comma(): void
    {
        $engine = app(AutomationEngine::class);
        $rule = \App\Models\AutomationRule::create([
            'name' => 'biweekly', 'is_enabled' => true, 'trigger_type' => 'scheduled',
            'trigger_config' => ['schedule_type' => 'monthly', 'day_of_month' => '5,20', 'time' => '08:00', 'target_model' => \App\Models\Customer::class],
            'actions' => [['type' => 'log_activity', 'description' => 'fired']],
        ]);

        $this->assertTrue($engine->isScheduledRuleDue($rule,  \Illuminate\Support\Carbon::create(2026, 6,  5, 8, 0)));
        $this->assertTrue($engine->isScheduledRuleDue($rule,  \Illuminate\Support\Carbon::create(2026, 6, 20, 8, 0)));
        $this->assertFalse($engine->isScheduledRuleDue($rule, \Illuminate\Support\Carbon::create(2026, 6, 10, 8, 0)));
        $this->assertFalse($engine->isScheduledRuleDue($rule, \Illuminate\Support\Carbon::create(2026, 6, 20, 9, 0)));

        $this->assertSame('0 8 5,20 * *', $engine->buildCronExpression($rule->trigger_config));
    }

    public function test_multi_day_of_week_with_comma(): void
    {
        $engine = app(AutomationEngine::class);
        $this->assertSame('0 9 * * 1,4', $engine->buildCronExpression([
            'schedule_type' => 'weekly', 'day_of_week' => '1,4', 'time' => '09:00',
        ]));
    }

    public function test_day_of_month_range_and_step(): void
    {
        $engine = app(AutomationEngine::class);
        $this->assertSame('0 8 1-5 * *',  $engine->buildCronExpression(['schedule_type' => 'monthly', 'day_of_month' => '1-5',  'time' => '08:00']));
        $this->assertSame('0 8 */7 * *',  $engine->buildCronExpression(['schedule_type' => 'monthly', 'day_of_month' => '*/7',  'time' => '08:00']));
    }

    public function test_invalid_day_of_month_falls_back_safely(): void
    {
        $engine = app(AutomationEngine::class);
        // injection attempt → falls back to '1', not raw passthrough
        $this->assertSame('0 8 1 * *', $engine->buildCronExpression([
            'schedule_type' => 'monthly', 'day_of_month' => "5; DROP TABLE", 'time' => '08:00',
        ]));
    }

}
