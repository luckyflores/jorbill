<?php

namespace App\Services\Network\Contracts;

interface MikrotikClient
{
    public function connect(): bool;

    public function disconnect(): void;

    /** @return array<int, array<string, mixed>> */
    public function listPppoeSecrets(): array;

    public function addPppoeSecret(string $name, string $password, string $profile, ?string $framedIp = null): bool;

    public function removePppoeSecret(string $name): bool;

    public function disconnectPppoeUser(string $name): bool;

    /** @return array<int, array<string, mixed>> */
    public function listActivePppoe(): array;

    /** @return array<int, array<string, mixed>> */
    public function listSimpleQueues(): array;

    public function addSimpleQueue(string $name, string $target, string $maxLimit): bool;

    public function removeSimpleQueue(string $name): bool;
}
