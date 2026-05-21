<?php

namespace App\Services\Network\Null;

use App\Services\Network\Contracts\MikrotikClient;
use Illuminate\Support\Facades\Log;

class NullMikrotikClient implements MikrotikClient
{
    public function connect(): bool
    {
        Log::info('NullMikrotikClient::connect');
        return true;
    }

    public function disconnect(): void
    {
        Log::info('NullMikrotikClient::disconnect');
    }

    public function listPppoeSecrets(): array
    {
        Log::info('NullMikrotikClient::listPppoeSecrets');
        return [];
    }

    public function addPppoeSecret(string $name, string $password, string $profile, ?string $framedIp = null): bool
    {
        Log::info('NullMikrotikClient::addPppoeSecret', [
            'name' => $name, 'profile' => $profile, 'framedIp' => $framedIp,
        ]);
        return true;
    }

    public function removePppoeSecret(string $name): bool
    {
        Log::info('NullMikrotikClient::removePppoeSecret', ['name' => $name]);
        return true;
    }

    public function disconnectPppoeUser(string $name): bool
    {
        Log::info('NullMikrotikClient::disconnectPppoeUser', ['name' => $name]);
        return true;
    }

    public function listActivePppoe(): array
    {
        Log::info('NullMikrotikClient::listActivePppoe');
        return [];
    }

    public function listSimpleQueues(): array
    {
        Log::info('NullMikrotikClient::listSimpleQueues');
        return [];
    }

    public function addSimpleQueue(string $name, string $target, string $maxLimit): bool
    {
        Log::info('NullMikrotikClient::addSimpleQueue', [
            'name' => $name, 'target' => $target, 'maxLimit' => $maxLimit,
        ]);
        return true;
    }

    public function removeSimpleQueue(string $name): bool
    {
        Log::info('NullMikrotikClient::removeSimpleQueue', ['name' => $name]);
        return true;
    }
    public function configureRadius(string $serverIp, string $sharedSecret): bool
    {
        Log::info('NullMikrotikClient::configureRadius', compact('serverIp'));
        return true;
    }

}
