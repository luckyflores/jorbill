<?php

namespace App\Services\Notifications\Drivers;

use App\Services\Notifications\Contracts\Notifier;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogNotifier implements Notifier
{
    public function id(): string { return 'log'; }

    public function send(string $to, string $body, array $context = []): ?string
    {
        $ref = 'LOG-' . Str::random(10);
        Log::channel(config('notifications.log_channel', 'stack'))
            ->info("[notif] to={$to} body={$body}", $context + ['ref' => $ref]);
        return $ref;
    }
}
