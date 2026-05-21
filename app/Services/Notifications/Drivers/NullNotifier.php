<?php

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NullNotifier implements Notifier
{
    public function id(): string { return 'null'; }

    public function send(string $to, string $body, array $context = []): ?string
    {
        $ref = 'NULL-' . Str::random(10);
        Log::info('NullNotifier::send', compact('to', 'body', 'context', 'ref'));
        return $ref;
    }
}
