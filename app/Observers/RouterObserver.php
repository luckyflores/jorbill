<?php

namespace App\Observers;

use App\Models\RadiusNas;
use App\Models\Router;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class RouterObserver
{
    public function saved(Router $router): void
    {
        $changed = false;

        if (! $router->public_ip || ! $router->radius_shared_secret) {
            $original = $router->getOriginal('public_ip');
            if ($original) {
                RadiusNas::where('nasname', $original)->delete();
                $changed = true;
            }
            $this->maybeReloadFreeRadius($changed);
            return;
        }

        $original = $router->getOriginal('public_ip');
        if ($original && $original !== $router->public_ip) {
            RadiusNas::where('nasname', $original)->delete();
            $changed = true;
        }

        $nas = RadiusNas::updateOrCreate(
            ['nasname' => $router->public_ip],
            [
                'shortname'   => substr($router->name, 0, 32),
                'type'        => 'mikrotik',
                'secret'      => $router->radius_shared_secret,
                'description' => ($router->location ?? '') . ' (auto-synced from JorBill)',
            ]
        );
        $changed = $changed || $nas->wasRecentlyCreated || $nas->wasChanged();
        $this->maybeReloadFreeRadius($changed);
    }

    public function deleted(Router $router): void
    {
        if ($router->public_ip) {
            RadiusNas::where('nasname', $router->public_ip)->delete();
            $this->maybeReloadFreeRadius(true);
        }
    }

    /**
     * Reload FreeRADIUS so `read_clients=yes` picks up the updated nas table.
     * Requires NOPASSWD sudo for systemctl (lucky user has it).
     * Adds ~1-2s latency to the save — acceptable for an admin task.
     */
    private function maybeReloadFreeRadius(bool $changed): void
    {
        if (! $changed) return;

        $process = new Process(['sudo', '-n', 'systemctl', 'restart', 'freeradius']);
        $process->setTimeout(8);
        try {
            $process->run();
            if (! $process->isSuccessful()) {
                Log::warning('RouterObserver: freeradius restart failed', [
                    'exit'  => $process->getExitCode(),
                    'stderr'=> $process->getErrorOutput(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('RouterObserver: freeradius restart threw', ['error' => $e->getMessage()]);
        }
    }
}
