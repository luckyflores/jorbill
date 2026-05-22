<?php

namespace App\Services\Network\Live;

use App\Services\Network\Contracts\GenieAcsClient;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class LiveGenieAcsClient implements GenieAcsClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $username = null,
        private readonly ?string $password = null,
        private readonly int $timeout = 10,
    ) {}

    private function http(): PendingRequest
    {
        $r = Http::baseUrl(rtrim($this->baseUrl, '/'))->timeout($this->timeout)->acceptJson();
        if ($this->username) {
            $r = $r->withBasicAuth($this->username, $this->password ?? '');
        }
        return $r;
    }

    public function listDevices(): array
    {
        try {
            $r = $this->http()->get('/devices');
            return $r->successful() ? ($r->json() ?? []) : [];
        } catch (Throwable $e) {
            Log::error('LiveGenieAcsClient::listDevices', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function getDevice(string $deviceId): ?array
    {
        try {
            $query = json_encode(['_id' => $deviceId]);
            $r = $this->http()->get('/devices', ['query' => $query]);
            if (! $r->successful()) return null;
            $devices = $r->json() ?? [];
            return $devices[0] ?? null;
        } catch (Throwable $e) {
            Log::error('LiveGenieAcsClient::getDevice', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function findDeviceBySerial(string $serialNumber): ?array
    {
        try {
            $query = json_encode(['_deviceId._SerialNumber' => $serialNumber]);
            $r = $this->http()->get('/devices', ['query' => $query]);
            if (! $r->successful()) return null;
            $devices = $r->json() ?? [];
            return $devices[0] ?? null;
        } catch (Throwable $e) {
            Log::error('LiveGenieAcsClient::findDeviceBySerial', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function setParameter(string $deviceId, string $parameterName, mixed $value): bool
    {
        try {
            $r = $this->http()
                ->post('/devices/' . rawurlencode($deviceId) . '/tasks?connection_request', [
                    'name' => 'setParameterValues',
                    'parameterValues' => [[ $parameterName, $value, $this->guessType($value) ]],
                ]);
            return $r->status() === 200 || $r->status() === 202;
        } catch (Throwable $e) {
            Log::error('LiveGenieAcsClient::setParameter', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function reboot(string $deviceId): bool
    {
        try {
            $r = $this->http()
                ->post('/devices/' . rawurlencode($deviceId) . '/tasks?connection_request', [
                    'name' => 'reboot',
                ]);
            return in_array($r->status(), [200, 202], true);
        } catch (Throwable $e) {
            Log::error('LiveGenieAcsClient::reboot', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function pushFirmware(string $deviceId, string $firmwareUrl): bool
    {
        try {
            $r = $this->http()
                ->post('/devices/' . rawurlencode($deviceId) . '/tasks?connection_request', [
                    'name'     => 'download',
                    'fileType' => '1 Firmware Upgrade Image',
                    'url'      => $firmwareUrl,
                ]);
            return in_array($r->status(), [200, 202], true);
        } catch (Throwable $e) {
            Log::error('LiveGenieAcsClient::pushFirmware', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getInformedAt(string $deviceId): ?DateTimeInterface
    {
        $device = $this->getDevice($deviceId);
        $last = $device['_lastInform'] ?? null;
        if (! $last) return null;
        try {
            return new DateTimeImmutable($last);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function guessType(mixed $value): string
    {
        return match (true) {
            is_bool($value)   => 'xsd:boolean',
            is_int($value)    => 'xsd:int',
            is_float($value)  => 'xsd:unsignedInt',
            default           => 'xsd:string',
        };
    }
}
