<?php

namespace App\Services\Automation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ContextBuilder
{
    public function build(Model $model): array
    {
        $ctx = [];
        $baseKey = $this->keyFor($model);
        $base = $this->modelToArray($model);
        $base = array_merge($base, $this->computedFieldsFor($model));
        $ctx[$baseKey] = $base;

        foreach (['customer', 'service', 'invoice', 'subscription', 'router', 'nap'] as $rel) {
            if (method_exists($model, $rel)) {
                try {
                    $related = $model->$rel;
                    if ($related instanceof Model) {
                        $arr = $this->modelToArray($related);
                        $arr = array_merge($arr, $this->computedFieldsFor($related));
                        $ctx[$rel] = $arr;
                    }
                } catch (\Throwable $e) {
                    // skip
                }
            }
        }

        return $ctx;
    }

    /**
     * Compute virtual fields that aren't DB columns but are commonly needed in conditions.
     */
    public function computedFieldsFor(Model $model): array
    {
        $out = [];
        $class = class_basename($model);

        if ($class === 'Invoice') {
            if ($due = $model->due_at ?? null) {
                $dueDate = $due instanceof Carbon ? $due : Carbon::parse($due);
                $out['days_overdue'] = max(0, now()->startOfDay()->diffInDays($dueDate->startOfDay(), false) * -1);
                $out['days_until_due'] = max(0, now()->startOfDay()->diffInDays($dueDate->startOfDay(), false));
            }
            $total = $model->total_centavos ?? 0;
            $paid = $model->amount_paid_centavos ?? 0;
            $out['amount_due_centavos'] = max(0, $total - $paid);
        }

        if ($class === 'Subscription') {
            if ($nbd = $model->next_billing_date ?? null) {
                $nbdDate = $nbd instanceof Carbon ? $nbd : Carbon::parse($nbd);
                $out['days_until_next_billing'] = now()->startOfDay()->diffInDays($nbdDate->startOfDay(), false);
            }
            if ($ac = $model->activated_at ?? null) {
                $acDate = $ac instanceof Carbon ? $ac : Carbon::parse($ac);
                $out['days_since_activated'] = abs(now()->startOfDay()->diffInDays($acDate->startOfDay(), false));
            }
        }

        if ($class === 'Customer') {
            if ($ac = $model->activated_at ?? null) {
                $acDate = $ac instanceof Carbon ? $ac : Carbon::parse($ac);
                $out['days_since_activated'] = abs(now()->startOfDay()->diffInDays($acDate->startOfDay(), false));
            }
        }

        return $out;
    }

    private function keyFor(Model $model): string
    {
        return strtolower(class_basename($model));
    }

    private function modelToArray(Model $model): array
    {
        return $model->attributesToArray();
    }
}
