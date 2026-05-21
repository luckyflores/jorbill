<?php

namespace App\Services\Network\Contracts;

use DateTimeInterface;

interface UispClient
{
    /** @return array<int, array<string, mixed>> */
    public function listDevices(): array;

    /** @return array<string, mixed>|null */
    public function getDevice(string $deviceId): ?array;

    /** @return array<int, array<string, mixed>> */
    public function listClients(): array;

    /** @return array<string, mixed>|null */
    public function getClient(string $clientId): ?array;

    /** @return array<int, array<string, mixed>> */
    public function getOutages(?DateTimeInterface $since = null): array;

    public function suspendClient(string $clientId): bool;

    public function unsuspendClient(string $clientId): bool;
}
