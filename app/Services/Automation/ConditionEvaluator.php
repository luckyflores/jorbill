<?php

namespace App\Services\Automation;

class ConditionEvaluator
{
    /**
     * Evaluate an AND-chain of conditions. Empty array returns true (no conditions = always match).
     * Each condition: {field: 'subscription.status', operator: 'eq', value: 'active'}
     */
    public function evaluate(array $conditions, array $context): bool
    {
        if (empty($conditions)) return true;

        foreach ($conditions as $cond) {
            if (! $this->evaluateOne($cond, $context)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateOne(array $cond, array $context): bool
    {
        $field    = $cond['field']    ?? null;
        $operator = $cond['operator'] ?? 'eq';
        $expected = $cond['value']    ?? null;

        if (! $field) return false;

        $actual = data_get($context, $field);

        return match ($operator) {
            'eq'          => $actual == $expected,
            'ne'          => $actual != $expected,
            'in'          => in_array($actual, $this->toArray($expected), false),
            'not_in'      => ! in_array($actual, $this->toArray($expected), false),
            'gt'          => $actual !== null && $actual > $expected,
            'lt'          => $actual !== null && $actual < $expected,
            'contains'    => $actual !== null && str_contains((string) $actual, (string) $expected),
            'is_null'     => $actual === null,
            'is_not_null' => $actual !== null,
            default       => false,
        };
    }

    private function toArray(mixed $value): array
    {
        if (is_array($value)) return $value;
        return array_map('trim', explode(',', (string) $value));
    }
}
