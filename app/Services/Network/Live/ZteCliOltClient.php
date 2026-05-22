<?php

namespace App\Services\Network\Live;

use App\Services\Network\Contracts\OltClient;
use Illuminate\Support\Facades\Log;
use phpseclib3\Net\SSH2;
use Throwable;

/**
 * ZTE-CLI compatible OLT driver — works with real ZTE ZXA10 C300/C600
 * AND most OEM clones that mirror the ZTE syntax (Lambda, C-Data, some V-SOL).
 *
 * Designed defensively:
 *  - Prompt pattern + save command are per-Olt configurable
 *  - rawCommand() escape hatch for vendor-specific quirks
 *  - All structured methods log warnings + return safe defaults on parse failures
 *  - Connect/disconnect lifecycle the caller controls
 */
class ZteCliOltClient implements OltClient
{
    private ?SSH2 $ssh = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly ?string $enablePassword = null,
        private readonly string $promptPattern = '[#>]',
        private readonly string $saveCommand = 'write',
        private readonly int $timeout = 10,
    ) {}

    public function connect(): bool
    {
        try {
            $this->ssh = new SSH2($this->host, $this->port, $this->timeout);
            $this->ssh->setTimeout($this->timeout);
            if (! $this->ssh->login($this->user, $this->password)) {
                Log::warning('ZteCliOltClient: SSH login failed', ['host' => $this->host]);
                $this->ssh = null;
                return false;
            }

            // wait for initial prompt
            $this->readUntilPrompt();

            // enter privileged mode
            $this->send('en');
            if ($this->enablePassword) {
                $this->ssh->read('assword:');
                $this->send($this->enablePassword);
            }

            // disable pagination so multi-page output streams as one chunk
            $this->send('terminal length 0');

            return true;
        } catch (Throwable $e) {
            Log::error('ZteCliOltClient::connect threw', ['error' => $e->getMessage()]);
            $this->ssh = null;
            return false;
        }
    }

    public function disconnect(): void
    {
        if ($this->ssh) {
            try { $this->ssh->disconnect(); } catch (Throwable $e) {}
            $this->ssh = null;
        }
    }

    private function send(string $cmd): string
    {
        if (! $this->ssh) return '';
        $this->ssh->write($cmd . "\n");
        return $this->readUntilPrompt();
    }

    private function readUntilPrompt(): string
    {
        return (string) $this->ssh->read('/' . $this->promptPattern . '\s*$/m');
    }

    public function rawCommand(string $command): ?string
    {
        if (! $this->ssh) return null;
        try {
            return $this->send($command);
        } catch (Throwable $e) {
            Log::error('ZteCliOltClient::rawCommand', ['cmd' => $command, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function listPonPorts(): array
    {
        $out = $this->send('show interface gpon-olt brief');
        $ports = [];
        // typical line: "gpon-olt_1/2/1   up   ..."
        if (preg_match_all('/gpon-olt_(\d+)\/(\d+)\/(\d+)\s+(\S+)/i', $out, $m, PREG_SET_ORDER)) {
            foreach ($m as $row) {
                $ports[] = [
                    'shelf'      => (int) $row[1],
                    'slot'       => (int) $row[2],
                    'port'       => (int) $row[3],
                    'identifier' => "gpon-olt_{$row[1]}/{$row[2]}/{$row[3]}",
                    'status'     => $row[4],
                ];
            }
        }
        return $ports;
    }

    public function listOnusOnPort(string $ponIdentifier): array
    {
        $out = $this->send("show gpon onu state {$ponIdentifier}");
        $onus = [];
        // typical: "gpon-onu_1/2/1:1   ZTEGC1234567   ready   working"
        if (preg_match_all(
            '/gpon-onu_\d+\/\d+\/\d+:(\d+)\s+(\S+)\s+(\S+)\s+(\S+)/i',
            $out, $m, PREG_SET_ORDER
        )) {
            foreach ($m as $row) {
                $onus[] = [
                    'onu_id'        => (int) $row[1],
                    'serial_number' => $row[2],
                    'admin_state'   => $row[3],
                    'oper_state'    => $row[4],
                    'pon'           => $ponIdentifier,
                ];
            }
        }
        return $onus;
    }

    public function listUnconfiguredOnus(): array
    {
        $out = $this->send('show pon onu uncfg');
        $onus = [];
        // typical: "gpon-olt_1/2/1   1   ZTEGC1234567"
        if (preg_match_all(
            '/gpon-olt_(\d+\/\d+\/\d+)\s+(\d+)\s+(\S+)/i',
            $out, $m, PREG_SET_ORDER
        )) {
            foreach ($m as $row) {
                $onus[] = [
                    'pon'           => "gpon-olt_{$row[1]}",
                    'detected_id'   => (int) $row[2],
                    'serial_number' => $row[3],
                ];
            }
        }
        return $onus;
    }

    public function getOnuStatus(string $ponIdentifier, int $onuId): ?array
    {
        $onuIfc = str_replace('gpon-olt_', 'gpon-onu_', $ponIdentifier) . ':' . $onuId;
        $detail = $this->send("show gpon onu detail-info {$onuIfc}");
        $power  = $this->send("show pon power attenuation {$onuIfc}");

        $status = ['pon' => $ponIdentifier, 'onu_id' => $onuId, 'raw_detail' => $detail, 'raw_power' => $power];

        if (preg_match('/Run state\s*:\s*(\S+)/i', $detail, $m)) {
            $status['run_state'] = $m[1];
        }
        if (preg_match('/Config state\s*:\s*(\S+)/i', $detail, $m)) {
            $status['config_state'] = $m[1];
        }
        if (preg_match('/Serial number\s*:\s*(\S+)/i', $detail, $m)) {
            $status['serial_number'] = $m[1];
        }
        if (preg_match('/Distance\s*:\s*(\d+)/i', $detail, $m)) {
            $status['distance_m'] = (int) $m[1];
        }
        // power: "ONU Rx ... -22.45 dBm"
        if (preg_match('/Rx[^\-0-9]*(-?\d+\.\d+)\s*dBm/i', $power, $m)) {
            $status['rx_power_dbm'] = (float) $m[1];
        }
        if (preg_match('/Tx[^\-0-9]*(-?\d+\.\d+)\s*dBm/i', $power, $m)) {
            $status['tx_power_dbm'] = (float) $m[1];
        }

        return $status;
    }

    public function authorizeOnu(
        string $ponIdentifier,
        int $onuId,
        string $serialNumber,
        string $onuType,
        ?string $tcontProfile = null,
        ?int $vlan = null,
        ?string $name = null,
    ): bool {
        try {
            $this->send('configure terminal');
            $this->send("interface {$ponIdentifier}");
            $this->send("onu {$onuId} type {$onuType} sn {$serialNumber}");
            $this->send('exit');

            $onuIfc = str_replace('gpon-olt_', 'gpon-onu_', $ponIdentifier) . ':' . $onuId;
            $this->send("interface {$onuIfc}");

            if ($name) {
                $this->send("name \"{$name}\"");
            }
            if ($tcontProfile) {
                $this->send("tcont 1 profile {$tcontProfile}");
                $this->send('gemport 1 tcont 1');
            }
            if ($vlan) {
                $this->send("service-port 1 vport 1 user-vlan {$vlan} vlan {$vlan}");
            }
            $this->send('exit');
            $this->send('exit');

            return $this->saveConfig();
        } catch (Throwable $e) {
            Log::error('ZteCliOltClient::authorizeOnu', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function deauthorizeOnu(string $ponIdentifier, int $onuId): bool
    {
        try {
            $this->send('configure terminal');
            $this->send("interface {$ponIdentifier}");
            $this->send("no onu {$onuId}");
            $this->send('exit');
            $this->send('exit');
            return $this->saveConfig();
        } catch (Throwable $e) {
            Log::error('ZteCliOltClient::deauthorizeOnu', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function rebootOnu(string $ponIdentifier, int $onuId): bool
    {
        try {
            $onuIfc = str_replace('gpon-olt_', 'gpon-onu_', $ponIdentifier) . ':' . $onuId;
            $this->send('configure terminal');
            $this->send("pon-onu-mng {$onuIfc}");
            $this->send('reboot');
            $this->send('exit');
            $this->send('exit');
            return true;
        } catch (Throwable $e) {
            Log::error('ZteCliOltClient::rebootOnu', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function saveConfig(): bool
    {
        try {
            $this->send($this->saveCommand);
            return true;
        } catch (Throwable $e) {
            Log::error('ZteCliOltClient::saveConfig', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
