<?php

namespace App\Observers;

use App\Models\RadiusNas;
use App\Models\Router;

class RouterObserver
{
    public function saved(Router $router): void
    {
        // Only sync if both public_ip and shared secret are set
        if (! $router->public_ip || ! $router->radius_shared_secret) {
            // If it previously had a public_ip, remove the stale nas entry
            $original = $router->getOriginal('public_ip');
            if ($original) {
                RadiusNas::where('nasname', $original)->delete();
            }
            return;
        }

        // If public_ip changed, remove the old nas row first
        $original = $router->getOriginal('public_ip');
        if ($original && $original !== $router->public_ip) {
            RadiusNas::where('nasname', $original)->delete();
        }

        RadiusNas::updateOrCreate(
            ['nasname' => $router->public_ip],
            [
                'shortname'   => substr($router->name, 0, 32),
                'type'        => 'mikrotik',
                'secret'      => $router->radius_shared_secret,
                'description' => ($router->location ?? '') . ' (auto-synced from JorBill)',
            ]
        );
    }

    public function deleted(Router $router): void
    {
        if ($router->public_ip) {
            RadiusNas::where('nasname', $router->public_ip)->delete();
        }
    }
}
