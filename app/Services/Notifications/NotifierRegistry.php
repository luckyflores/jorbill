<?php

namespace App\Services\Notifications;

use App\Services\Notifications\Contracts\Notifier;

class NotifierRegistry
{
    /** @var array<string, Notifier> */
    private array $notifiers = [];

    public function register(Notifier $notifier): void
    {
        $this->notifiers[$notifier->id()] = $notifier;
    }

    public function forChannel(string $channel): ?Notifier
    {
        return $this->notifiers[$channel] ?? null;
    }

    /** @return string[] */
    public function availableChannels(): array
    {
        return array_keys($this->notifiers);
    }

    /** @return array<string,string>  ['semaphore' => 'Semaphore', ...]  for Filament selects */
    public function options(): array
    {
        $labels = [
            'null'      => 'Null (no-op)',
            'log'       => 'Log (writes to laravel log)',
            'semaphore' => 'Semaphore SMS',
            'globe'     => 'Globe SMS (M360 / Labs)',
            'whatsapp'  => 'WhatsApp Cloud API',
        ];
        $out = [];
        foreach ($this->availableChannels() as $id) {
            $out[$id] = $labels[$id] ?? ucfirst($id);
        }
        return $out;
    }
}
