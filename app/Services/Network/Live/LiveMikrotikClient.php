<?php

namespace App\Services\Network\Live;

use App\Services\Network\Contracts\MikrotikClient;
use Illuminate\Support\Facades\Log;
use RouterOS\Client;
use RouterOS\Query;
use Throwable;

class LiveMikrotikClient implements MikrotikClient
{
    private ?Client $client = null;

    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly string $user,
        private readonly string $password,
        private readonly int $timeout = 5,
    ) {}

    public function connect(): bool
    {
        try {
            $this->client = new Client([
                'host'    => $this->host,
                'port'    => $this->port,
                'user'    => $this->user,
                'pass'    => $this->password,
                'timeout' => $this->timeout,
            ]);
            return true;
        } catch (Throwable $e) {
            Log::warning('LiveMikrotikClient::connect failed', [
                'host'  => $this->host,
                'error' => $e->getMessage(),
            ]);
            $this->client = null;
            return false;
        }
    }

    public function disconnect(): void
    {
        $this->client = null;
    }

    public function listPppoeSecrets(): array
    {
        if (! $this->client) return [];
        try {
            return $this->client->query(new Query('/ppp/secret/print'))->read();
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::listPppoeSecrets', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function addPppoeSecret(string $name, string $password, string $profile, ?string $framedIp = null): bool
    {
        if (! $this->client) return false;
        try {
            // idempotent: update if exists, else add
            $existing = $this->client->query(
                (new Query('/ppp/secret/print'))->where('name', $name)
            )->read();

            if (! empty($existing)) {
                $q = (new Query('/ppp/secret/set'))
                    ->equal('.id', $existing[0]['.id'])
                    ->equal('password', $password)
                    ->equal('profile', $profile);
                if ($framedIp) $q->equal('remote-address', $framedIp);
                $this->client->query($q)->read();
                Log::info('LiveMikrotikClient::addPppoeSecret updated', ['name' => $name]);
                return true;
            }

            $q = (new Query('/ppp/secret/add'))
                ->equal('name', $name)
                ->equal('password', $password)
                ->equal('service', 'pppoe')
                ->equal('profile', $profile);
            if ($framedIp) $q->equal('remote-address', $framedIp);
            $this->client->query($q)->read();
            Log::info('LiveMikrotikClient::addPppoeSecret created', ['name' => $name]);
            return true;
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::addPppoeSecret', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function removePppoeSecret(string $name): bool
    {
        if (! $this->client) return false;
        try {
            $existing = $this->client->query(
                (new Query('/ppp/secret/print'))->where('name', $name)
            )->read();
            if (empty($existing)) return true; // already gone ? idempotent

            $this->client->query(
                (new Query('/ppp/secret/remove'))->equal('.id', $existing[0]['.id'])
            )->read();
            return true;
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::removePppoeSecret', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function disconnectPppoeUser(string $name): bool
    {
        if (! $this->client) return false;
        try {
            $active = $this->client->query(
                (new Query('/ppp/active/print'))->where('name', $name)
            )->read();
            if (empty($active)) return true;

            $this->client->query(
                (new Query('/ppp/active/remove'))->equal('.id', $active[0]['.id'])
            )->read();
            return true;
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::disconnectPppoeUser', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function listActivePppoe(): array
    {
        if (! $this->client) return [];
        try {
            return $this->client->query(new Query('/ppp/active/print'))->read();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function listSimpleQueues(): array
    {
        if (! $this->client) return [];
        try {
            return $this->client->query(new Query('/queue/simple/print'))->read();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function addSimpleQueue(string $name, string $target, string $maxLimit): bool
    {
        if (! $this->client) return false;
        try {
            $existing = $this->client->query(
                (new Query('/queue/simple/print'))->where('name', $name)
            )->read();

            if (! empty($existing)) {
                $this->client->query(
                    (new Query('/queue/simple/set'))
                        ->equal('.id', $existing[0]['.id'])
                        ->equal('target', $target)
                        ->equal('max-limit', $maxLimit)
                )->read();
                return true;
            }

            $this->client->query(
                (new Query('/queue/simple/add'))
                    ->equal('name', $name)
                    ->equal('target', $target)
                    ->equal('max-limit', $maxLimit)
            )->read();
            return true;
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::addSimpleQueue', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function removeSimpleQueue(string $name): bool
    {
        if (! $this->client) return false;
        try {
            $existing = $this->client->query(
                (new Query('/queue/simple/print'))->where('name', $name)
            )->read();
            if (empty($existing)) return true;

            $this->client->query(
                (new Query('/queue/simple/remove'))->equal('.id', $existing[0]['.id'])
            )->read();
            return true;
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::removeSimpleQueue', ['error' => $e->getMessage()]);
            return false;
        }
    }
    public function configureRadius(string $serverIp, string $sharedSecret): bool
    {
        if (! $this->client) return false;
        try {
            // /radius: idempotent — check existing entries with matching address
            $existing = $this->client->query(
                (new Query('/radius/print'))->where('address', $serverIp)
            )->read();

            if (! empty($existing)) {
                $this->client->query(
                    (new Query('/radius/set'))
                        ->equal('.id', $existing[0]['.id'])
                        ->equal('secret', $sharedSecret)
                        ->equal('service', 'ppp,login')
                        ->equal('timeout', '3s')
                )->read();
            } else {
                $this->client->query(
                    (new Query('/radius/add'))
                        ->equal('address', $serverIp)
                        ->equal('secret', $sharedSecret)
                        ->equal('service', 'ppp,login')
                        ->equal('timeout', '3s')
                )->read();
            }

            // Enable CoA on port 3799
            $this->client->query(
                (new Query('/radius/incoming/set'))
                    ->equal('accept', 'yes')
                    ->equal('port', '3799')
            )->read();

            // /ppp aaa: use RADIUS, fall back to local secrets if RADIUS unreachable
            $this->client->query(
                (new Query('/ppp/aaa/set'))
                    ->equal('use-radius', 'yes')
                    ->equal('use-radius-only', 'no')
            )->read();

            Log::info('LiveMikrotikClient::configureRadius success', ['serverIp' => $serverIp]);
            return true;
        } catch (Throwable $e) {
            Log::error('LiveMikrotikClient::configureRadius', ['error' => $e->getMessage()]);
            return false;
        }
    }

}
