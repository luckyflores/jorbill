<?php

namespace App\Services\Network\Null;

use App\Services\Network\Contracts\OltClient;
use Illuminate\Support\Facades\Log;

class NullOltClient implements OltClient
{
    public function connect(): bool { Log::info('NullOltClient::connect'); return true; }
    public function disconnect(): void { Log::info('NullOltClient::disconnect'); }
    public function rawCommand(string $command): ?string { Log::info('NullOltClient::rawCommand', compact('command')); return ''; }
    public function listPonPorts(): array { Log::info('NullOltClient::listPonPorts'); return []; }
    public function listOnusOnPort(string $ponIdentifier): array { Log::info('NullOltClient::listOnusOnPort', compact('ponIdentifier')); return []; }
    public function listUnconfiguredOnus(): array { Log::info('NullOltClient::listUnconfiguredOnus'); return []; }
    public function getOnuStatus(string $ponIdentifier, int $onuId): ?array { Log::info('NullOltClient::getOnuStatus', compact('ponIdentifier','onuId')); return null; }
    public function authorizeOnu(string $ponIdentifier, int $onuId, string $serialNumber, string $onuType, ?string $tcontProfile = null, ?int $vlan = null, ?string $name = null): bool { Log::info('NullOltClient::authorizeOnu', compact('ponIdentifier','onuId','serialNumber')); return true; }
    public function deauthorizeOnu(string $ponIdentifier, int $onuId): bool { Log::info('NullOltClient::deauthorizeOnu', compact('ponIdentifier','onuId')); return true; }
    public function rebootOnu(string $ponIdentifier, int $onuId): bool { Log::info('NullOltClient::rebootOnu', compact('ponIdentifier','onuId')); return true; }
    public function saveConfig(): bool { Log::info('NullOltClient::saveConfig'); return true; }
}
