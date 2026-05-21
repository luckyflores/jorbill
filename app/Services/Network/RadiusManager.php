<?php

namespace App\Services\Network;

use App\Models\RadiusSession;
use App\Models\Router;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RadiusManager
{
    /**
     * Kick an active session (RADIUS Disconnect-Message to NAS on port 3799).
     * Requires the NAS to have `/radius incoming set accept=yes` on Mikrotik.
     */
    public function kickSession(RadiusSession $session): bool
    {
        $router = Router::where('public_ip', (string) $session->nasipaddress)->first();
        if (! $router || ! $router->radius_shared_secret) {
            Log::warning('RadiusManager::kickSession: no router with matching public_ip + secret', [
                'nasipaddress' => (string) $session->nasipaddress,
            ]);
            return false;
        }

        $attrs = "User-Name = {$session->username}\nAcct-Session-Id = {$session->acctsessionid}\n";
        return $this->runRadclient('disconnect', $router, $attrs);
    }

    /**
     * Push a bandwidth update via CoA-Request for an active subscription.
     */
    public function updateBandwidth(Subscription $sub, int $downKbps, int $upKbps): bool
    {
        // find the most recent active session for this username
        $session = RadiusSession::active()
            ->where('username', $sub->username)
            ->orderByDesc('acctstarttime')
            ->first();

        if (! $session) {
            Log::info('RadiusManager::updateBandwidth: no active session — change will apply on next connect');
            return true;  // soft success: DB change is enough; next auth will use new bandwidth
        }

        $router = Router::where('public_ip', (string) $session->nasipaddress)->first();
        if (! $router || ! $router->radius_shared_secret) return false;

        $rate = "{$downKbps}k/{$upKbps}k";
        $attrs = "User-Name = {$sub->username}\nMikrotik-Rate-Limit = {$rate}\n";
        return $this->runRadclient('coa', $router, $attrs);
    }

    private function runRadclient(string $action, Router $router, string $attrs): bool
    {
        $target = "{$router->public_ip}:3799";
        $process = new Process(['radclient', '-x', '-t', '3', $target, $action, $router->radius_shared_secret]);
        $process->setInput($attrs);
        $process->setTimeout(10);

        try {
            $process->run();
        } catch (\Throwable $e) {
            Log::error('RadiusManager::radclient threw', ['error' => $e->getMessage()]);
            return false;
        }

        if (! $process->isSuccessful()) {
            Log::warning('RadiusManager::radclient non-zero exit', [
                'action' => $action, 'target' => $target,
                'exit'   => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
                'stdout' => $process->getOutput(),
            ]);
            return false;
        }

        // radclient returns 0 on Disconnect-ACK or CoA-ACK; check stdout for ACK string
        $out = $process->getOutput();
        $ok = str_contains($out, 'Disconnect-ACK') || str_contains($out, 'CoA-ACK');
        if (! $ok) {
            Log::warning('RadiusManager::radclient no ACK in output', ['output' => $out]);
        }
        return $ok;
    }
}
