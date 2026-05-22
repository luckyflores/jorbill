<?php

namespace App\Console\Commands;

use App\Models\AutomationRule;
use App\Services\Automation\AutomationEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Throwable;

class RunScheduledAutomationRules extends Command
{
    protected $signature = 'automation:run-scheduled
                             {--rule= : Run only this rule ID (force, ignores schedule)}
                             {--dry-run : Do not execute actions, just log what would happen}';

    protected $description = 'Evaluate enabled scheduled automation rules and fire those whose schedule matches the current minute.';

    public function handle(AutomationEngine $engine): int
    {
        $now = now();
        $forceRuleId = $this->option('rule');
        $dryRun = (bool) $this->option('dry-run');

        $rules = AutomationRule::query()
            ->where('is_enabled', true)
            ->where('trigger_type', 'scheduled')
            ->when($forceRuleId, fn ($q) => $q->where('id', $forceRuleId))
            ->get();

        $fired = 0;
        foreach ($rules as $rule) {
            if (! $forceRuleId && ! $engine->isScheduledRuleDue($rule, $now)) {
                continue;
            }

            $lock = Cache::lock("automation_rule_{$rule->id}", 600);
            if (! $lock->get()) {
                $this->warn("Rule #{$rule->id} ({$rule->name}) — already running, skipping this tick");
                continue;
            }

            try {
                $this->info("Firing rule #{$rule->id}: {$rule->name}" . ($dryRun ? ' [DRY-RUN]' : ''));
                $count = $engine->fireScheduledRule($rule, dryRun: $dryRun);
                $this->info("  → processed {$count} record(s)");
                $fired++;
            } catch (Throwable $e) {
                $this->error("Rule #{$rule->id} failed: " . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        $this->info("Done — fired {$fired} rule(s) at {$now->format('Y-m-d H:i:s')}");
        return self::SUCCESS;
    }
}
