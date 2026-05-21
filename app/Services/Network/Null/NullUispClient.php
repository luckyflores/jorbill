<?php

namespace App\Services\Network\Null;

use App\Services\Network\Contracts\UispClient;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;

class NullUispClient implements UispClient
{
    public function listDevices(): array
    {
        Log::info('NullUispClient::listDevices');
        return [];
    }

    public function getDevice(string $deviceId): ?array
    {
        Log::info('NullUispClient::getDevice', ['deviceId' => $deviceId]);
        return null;
    }

    public function listClients(): array
    {
        Log::info('NullUispClient::listClients');
        return [];
    }

    public function getClient(string $clientId): ?array
    {
        Log::info('NullUispClient::getClient', ['clientId' => $clientId]);
        return null;
    }

    public function getOutages(?DateTimeInterface $since = null): array
    {
        Log::info('NullUispClient::getOutages', ['since' => $since?->format(DATE_ATOM)]);
        return [];
    }

    public function suspendClient(string $clientId): bool
    {
        Log::info('NullUispClient::suspendClient', ['clientId' => $clientId]);
        return true;
    }

    public function unsuspendClient(string $clientId): bool
    {
        Log::info('NullUispClient::unsuspendClient', ['clientId' => $clientId]);
        return true;
    }
}
