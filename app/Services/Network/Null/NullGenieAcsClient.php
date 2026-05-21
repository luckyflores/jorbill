<?php

namespace App\Services\Network\Null;

use App\Services\Network\Contracts\GenieAcsClient;
use DateTimeInterface;
use Illuminate\Support\Facades\Log;

class NullGenieAcsClient implements GenieAcsClient
{
    public function listDevices(): array
    {
        Log::info('NullGenieAcsClient::listDevices');
        return [];
    }

    public function getDevice(string $deviceId): ?array
    {
        Log::info('NullGenieAcsClient::getDevice', ['deviceId' => $deviceId]);
        return null;
    }

    public function setParameter(string $deviceId, string $parameterName, mixed $value): bool
    {
        Log::info('NullGenieAcsClient::setParameter', [
            'deviceId' => $deviceId,
            'parameterName' => $parameterName,
            'value' => $value,
        ]);
        return true;
    }

    public function reboot(string $deviceId): bool
    {
        Log::info('NullGenieAcsClient::reboot', ['deviceId' => $deviceId]);
        return true;
    }

    public function pushFirmware(string $deviceId, string $firmwareUrl): bool
    {
        Log::info('NullGenieAcsClient::pushFirmware', [
            'deviceId' => $deviceId,
            'firmwareUrl' => $firmwareUrl,
        ]);
        return true;
    }

    public function getInformedAt(string $deviceId): ?DateTimeInterface
    {
        Log::info('NullGenieAcsClient::getInformedAt', ['deviceId' => $deviceId]);
        return null;
    }
}
