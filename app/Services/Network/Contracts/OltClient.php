<?php

namespace App\Services\Network\Contracts;

interface OltClient
{
    public function connect(): bool;

    public function disconnect(): void;

    /** Raw CLI escape hatch — sends arbitrary command, returns full output. */
    public function rawCommand(string $command): ?string;

    /** @return array<int, array<string, mixed>> — [{shelf, slot, port, identifier}] */
    public function listPonPorts(): array;

    /** ONUs detected on a PON port. @return array<int, array<string, mixed>> */
    public function listOnusOnPort(string $ponIdentifier): array;

    /** ONUs powered on but not yet provisioned. @return array<int, array<string, mixed>> */
    public function listUnconfiguredOnus(): array;

    /** Status + signal levels for one ONU. */
    public function getOnuStatus(string $ponIdentifier, int $onuId): ?array;

    /** Provision a new ONU on a PON port with the given serial and config. */
    public function authorizeOnu(
        string $ponIdentifier,
        int $onuId,
        string $serialNumber,
        string $onuType,
        ?string $tcontProfile = null,
        ?int $vlan = null,
        ?string $name = null,
    ): bool;

    /** Remove an ONU from the OLT config. */
    public function deauthorizeOnu(string $ponIdentifier, int $onuId): bool;

    /** Soft reboot an ONU. */
    public function rebootOnu(string $ponIdentifier, int $onuId): bool;

    /** Save running config to startup. */
    public function saveConfig(): bool;
}
