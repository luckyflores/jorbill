<?php

namespace App\Services\Automation;

use App\Models\AutomationRule;
use App\Models\AutomationRuleExecution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    /** Per-request recursion guard — prevent infinite loops if an action triggers another rule. */
    private int $depth = 0;
    private const MAX_DEPTH = 5;

    public function __construct(
        private readonly ContextBuilder $contextBuilder,
        private readonly ConditionEvaluator $conditionEvaluator,
        private readonly ActionRunner $actionRunner,
    ) {}

    /** Handler for eloquent.* events. $eventName like "eloquent.updated: App\Models\Subscription". */
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

        // skip events we don't care about (saving/retrieved/booting etc)
        if (! in_array($when, ['created', 'updated', 'deleted'], true)) return;

        // skip our own automation models to prevent self-trigger loops
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
            try {
                $this->fireRule($rule, $model);
            } finally {
                $this->depth--;
            }
        }
    }

    public function fireRule(AutomationRule $rule, Model $model, bool $dryRun = false): AutomationRuleExecution
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
            'fired_at'           => now(),
            'trigger_summary'    => class_basename($model) . " #{$model->getKey()} " . ($rule->trigger_config['when'] ?? '?'),
            'trigger_payload'    => $context,
            'conditions_matched' => $matched,
            'actions_executed'   => $results,
            'duration_ms'        => (int) round((microtime(true) - $start) * 1000),
        ]);

        if (! $dryRun && $matched) {
            $rule->forceFill([
                'last_fired_at' => now(),
                'fire_count'    => ($rule->fire_count ?? 0) + 1,
            ])->save();
        }

        return $exec;
    }
}
