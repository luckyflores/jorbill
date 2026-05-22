<?php

namespace App\Services\Automation;

use App\Models\AutomationRule;
use App\Models\AutomationRuleExecution;
use Cron\CronExpression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AutomationEngine
{
    private int $depth = 0;
    private const MAX_DEPTH = 5;

    public function __construct(
        private readonly ContextBuilder $contextBuilder,
        private readonly ConditionEvaluator $conditionEvaluator,
        private readonly ActionRunner $actionRunner,
    ) {}

    // ─────────────────────────────────────────────────────────
    // EVENT-DRIVEN (Phase A)
    // ─────────────────────────────────────────────────────────
    public function onModelEvent(string $eventName, array $payload): void
    {
        if (! preg_match('/^eloquent\.(\w+): (.+)$/', $eventName, $m)) return;
        $when = $m[1];
        $modelClass = $m[2];
        $model = $payload[0] ?? null;
        if (! ($model instanceof Model)) return;

        if ($this->depth >= self::MAX_DEPTH) {
            Log::warning('AutomationEngine: max recursion depth reached', ['when' => $when, 'model' => $modelClass]);
            return;
        }
        if (! in_array($when, ['created', 'updated', 'deleted'], true)) return;
        if (in_array($modelClass, [AutomationRule::class, AutomationRuleExecution::class], true)) return;

        $rules = AutomationRule::query()
            ->where('is_enabled', true)
            ->where('trigger_type', 'model')
            ->get()
            ->filter(fn ($r) => ($r->trigger_config['model'] ?? null) === $modelClass
                             && ($r->trigger_config['when']  ?? null) === $when);

        foreach ($rules as $rule) {
            $ifChanged = $rule->trigger_config['if_changed'] ?? null;
            if ($ifChanged && $when === 'updated' && ! $model->wasChanged($ifChanged)) {
                continue;
            }
            $this->depth++;
            try { $this->fireRule($rule, $model); } finally { $this->depth--; }
        }
    }

    // ─────────────────────────────────────────────────────────
    // SCHEDULED (Phase B)
    // ─────────────────────────────────────────────────────────

    public function isScheduledRuleDue(AutomationRule $rule, ?Carbon $now = null): bool
    {
        $now ??= now();
        try {
            $expression = $this->buildCronExpression($rule->trigger_config ?? []);
            return (new CronExpression($expression))->isDue($now);
        } catch (\Throwable $e) {
            Log::warning('AutomationEngine::isScheduledRuleDue invalid cron', [
                'rule_id' => $rule->id, 'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function buildCronExpression(array $config): string
    {
        $time = $config['time'] ?? '00:00';
        $parts = explode(':', $time);
        $h = (int) ($parts[0] ?? 0);
        $m = (int) ($parts[1] ?? 0);

        // Pass day_of_month / day_of_week as STRINGS so cron's native comma (5,20),
        // range (5-10), and step (*/7) syntax all work.
        $dom = trim((string) ($config['day_of_month'] ?? '1'));
        $dow = trim((string) ($config['day_of_week']  ?? '1'));
        // sanity: reject anything but digits + cron metachars
        if (! preg_match('/^[0-9,\-\/\*]+$/', $dom)) $dom = '1';
        if (! preg_match('/^[0-9,\-\/\*]+$/', $dow)) $dow = '1';

        return match ($config['schedule_type'] ?? 'daily') {
            'daily'   => "{$m} {$h} * * *",
            'weekly'  => "{$m} {$h} * * {$dow}",
            'monthly' => "{$m} {$h} {$dom} * *",
            'cron'    => $config['cron'] ?? '* * * * *',
            default   => '* * * * *',
        };
    }

    /**
     * Run a scheduled rule: query its target model with target_filter,
     * iterate matching records, evaluate conditions + execute actions per record.
     * Returns count of records processed.
     */
    public function fireScheduledRule(AutomationRule $rule, bool $dryRun = false, ?int $limit = null): int
    {
        $modelClass = $rule->trigger_config['target_model'] ?? null;
        if (! $modelClass || ! class_exists($modelClass)) {
            Log::warning('AutomationEngine::fireScheduledRule no target model', ['rule_id' => $rule->id]);
            return 0;
        }

        $batchId = (string) Str::ulid();
        $query = $modelClass::query();
        $this->applyTargetFilter($query, $rule->target_filter ?? []);
        if ($limit) $query->limit($limit);

        $count = 0;
        $query->chunkById(100, function ($records) use ($rule, $dryRun, $batchId, &$count) {
            foreach ($records as $record) {
                $this->depth++;
                try {
                    $this->fireRule($rule, $record, $dryRun, $batchId);
                } finally {
                    $this->depth--;
                }
                $count++;
            }
        });

        if (! $dryRun && $count > 0) {
            $rule->forceFill([
                'last_fired_at' => now(),
                'fire_count'    => ($rule->fire_count ?? 0) + 1,
            ])->save();
        }

        return $count;
    }

    private function applyTargetFilter(Builder $query, array $filter): void
    {
        foreach ($filter as $cond) {
            $field = $cond['field']    ?? null;
            $op    = $cond['operator'] ?? 'eq';
            $value = $cond['value']    ?? null;
            if (! $field) continue;

            // Computed-field translations — driver-aware (postgres in prod, sqlite in tests)
            $driver = $query->getModel()->getConnection()->getDriverName();
            $dayDiff = fn (string $col) => $driver === 'sqlite'
                ? "CAST((julianday(CURRENT_DATE) - julianday({$col})) AS INTEGER)"
                : "(CURRENT_DATE - {$col}::date)";

            if ($field === 'days_overdue' && in_array($op, ['gt','lt','eq','ne'], true)) {
                $expr = $dayDiff('due_at');
                $sqlOp = match ($op) { 'gt'=>'>', 'lt'=>'<', 'eq'=>'=', 'ne'=>'!=' };
                $query->whereRaw("{$expr} {$sqlOp} ?", [(int) $value]);
                continue;
            }
            if ($field === 'days_until_due' && in_array($op, ['gt','lt','eq','ne'], true)) {
                $expr = $driver === 'sqlite'
                    ? "CAST((julianday(due_at) - julianday(CURRENT_DATE)) AS INTEGER)"
                    : "(due_at::date - CURRENT_DATE)";
                $sqlOp = match ($op) { 'gt'=>'>', 'lt'=>'<', 'eq'=>'=', 'ne'=>'!=' };
                $query->whereRaw("{$expr} {$sqlOp} ?", [(int) $value]);
                continue;
            }
            if ($field === 'days_since_activated' && in_array($op, ['gt','lt','eq','ne'], true)) {
                $expr = $dayDiff('activated_at');
                $sqlOp = match ($op) { 'gt'=>'>', 'lt'=>'<', 'eq'=>'=', 'ne'=>'!=' };
                $query->whereRaw("{$expr} {$sqlOp} ?", [(int) $value]);
                continue;
            }

            // Standard columns
            match ($op) {
                'eq'          => $query->where($field, $value),
                'ne'          => $query->where($field, '!=', $value),
                'in'          => $query->whereIn($field, is_array($value) ? $value : array_map('trim', explode(',', (string) $value))),
                'not_in'      => $query->whereNotIn($field, is_array($value) ? $value : array_map('trim', explode(',', (string) $value))),
                'gt'          => $query->where($field, '>', $value),
                'lt'          => $query->where($field, '<', $value),
                'contains'    => $query->where($field, 'like', "%{$value}%"),
                'is_null'     => $query->whereNull($field),
                'is_not_null' => $query->whereNotNull($field),
                default       => null,
            };
        }
    }

    // ─────────────────────────────────────────────────────────
    // SHARED: fire one rule for one record
    // ─────────────────────────────────────────────────────────
    public function fireRule(AutomationRule $rule, Model $model, bool $dryRun = false, ?string $batchId = null): AutomationRuleExecution
    {
        $start = microtime(true);
        $context = $this->contextBuilder->build($model);

        $matched = $this->conditionEvaluator->evaluate($rule->conditions ?? [], $context);
        $results = [];

        if ($matched) {
            foreach (($rule->actions ?? []) as $action) {
                $results[] = $this->actionRunner->run($action, $context, $model, $dryRun);
            }
        }

        $exec = AutomationRuleExecution::create([
            'rule_id'            => $rule->id,
            'batch_id'           => $batchId,
            'fired_at'           => now(),
            'trigger_summary'    => class_basename($model) . " #{$model->getKey()}",
            'trigger_payload'    => $context,
            'conditions_matched' => $matched,
            'actions_executed'   => $results,
            'duration_ms'        => (int) round((microtime(true) - $start) * 1000),
        ]);

        if (! $dryRun && $matched && ! $batchId) {
            // for event-driven, fire_count bumped per fire; for scheduled, bumped once in fireScheduledRule
            $rule->forceFill([
                'last_fired_at' => now(),
                'fire_count'    => ($rule->fire_count ?? 0) + 1,
            ])->save();
        }

        return $exec;
    }
}
