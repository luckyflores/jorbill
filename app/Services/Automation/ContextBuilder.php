<?php

namespace App\Services\Automation;

use Illuminate\Database\Eloquent\Model;

/**
 * Builds a flat associative context array from a triggering model.
 * The model's table name becomes the key (e.g. "subscription"); attributes are flattened.
 * Eager-loadable relationships are walked one level deep for known associations.
 */
class ContextBuilder
{
    public function build(Model $model): array
    {
        $ctx = [];
        $baseKey = $this->keyFor($model);
        $ctx[$baseKey] = $this->modelToArray($model);

        // Walk known relationships one level deep
        foreach (['customer', 'service', 'invoice', 'subscription', 'router', 'nap'] as $rel) {
            if (method_exists($model, $rel)) {
                try {
                    $related = $model->$rel;
                    if ($related instanceof Model) {
                        $ctx[$rel] = $this->modelToArray($related);
                    }
                } catch (\Throwable $e) {
                    // skip silently
                }
            }
        }

        return $ctx;
    }

    private function keyFor(Model $model): string
    {
        $class = class_basename($model);
        return strtolower($class);
    }

    private function modelToArray(Model $model): array
    {
        return $model->attributesToArray();
    }
}
