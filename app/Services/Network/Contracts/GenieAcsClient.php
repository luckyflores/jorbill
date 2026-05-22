<?php

namespace App\Services\Network\Contracts;

use DateTimeInterface;

interface GenieAcsClient
{
    /** @return array<int, array<string, mixed>> */
    public function listDevices(): array;

    /** @return array<string, mixed>|null */
    public function getDevice(string $deviceId): ?array;

    public function setParameter(string $deviceId, string $parameterName, mixed $value): bool;

    public function reboot(string $deviceId): bool;

    public function pushFirmware(string $deviceId, string $firmwareUrl): bool;

    public function getInformedAt(string $deviceId): ?DateTimeInterface;
    /** Find a device by its TR-069 SerialNumber. Returns full device or null. */
    public function findDeviceBySerial(string $serialNumber): ?array;

}