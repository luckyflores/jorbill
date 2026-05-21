<?php

namespace App\Services\Notifications\Contracts;

interface Notifier
{
    public function id(): string;

    public function send(string $to, string $body, array $context = []): ?string;
}
