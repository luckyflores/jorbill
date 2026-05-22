<?php

namespace App\Services\Automation;

class Interpolator
{
    /** Replace {{path.to.field}} in $template with values from $context. */
    public function render(string $template, array $context): string
    {
        return preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/', function ($m) use ($context) {
            $value = data_get($context, $m[1]);
            return is_scalar($value) ? (string) $value : '';
        }, $template);
    }
}
