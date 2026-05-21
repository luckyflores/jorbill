<?php

namespace App\Services\Subscriptions;

use App\Models\Router;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\Network\Contracts\MikrotikClientFactory;
use Illuminate\Support\Facades\Log;

class SubscriptionProvisioner
{
    public function __construct(
        private readonly MikrotikClientFactory $factory,
    ) {}

    /**
     * Idempotently push subscription state to its router.
     * Returns true on success (incl. no-op), false on failure or missing config.
     */
    public function sync(Subscription $subscription): bool
    {
        if (! $subscription->router_id) {
            Log::info('SubscriptionProvisioner::sync skipped ? no router_id', ['sub_id' => $subscription->id]);
            return false;
        }

        $router = Router::find($subscription->router_id);
        if (! $router || ! $router->is_active) {
            Log::warning('SubscriptionProvisioner::sync skipped ? router missing or inactive', [
                'sub_id' => $subscription->id, 'router_id' => $subscription->router_id,
            ]);
            return false;
        }

        $service = Service::find($subscription->service_id);
        if (! $service) {
            Log::warning('SubscriptionProvisioner::sync skipped ? service missing', ['sub_id' => $subscription->id]);
            return false;
        }

        $client = $this->factory->forRouter($router);
        if (! $client->connect()) {
            Log::error('SubscriptionProvisioner::sync ? connect failed', ['router_id' => $router->id]);
            return false;
        }

        try {
            $shouldExist = $subscription->status === 'active';

            return match ($service->type) {
                'pppoe'  => $this->syncPppoe($client, $subscription, $service, $shouldExist),
                'ipoe', 'static' => $this->syncQueue($client, $subscription, $service, $shouldExist),
                'hotspot' => true, // TODO: voucher-based hotspot provisioning (Phase 5)
                default => false,
            };
        } finally {
            $client->disconnect();
        }
    }

    private function syncPppoe($client, Subscription $sub, Service $svc, bool $shouldExist): bool
    {
        if (! $sub->username) {
            Log::warning('PPPoE sub missing username', ['sub_id' => $sub->id]);
            return false;
        }

        if ($shouldExist) {
            return $client->addPppoeSecret(
                name: $sub->username,
                password: $sub->password ?? '',  // encrypted cast decrypts on read
                profile: $svc->mikrotik_profile_name ?? 'default',
                framedIp: $sub->ip_address,
            );
        }

        $ok = $client->removePppoeSecret($sub->username);
        // best-effort kick if currently connected
        $client->disconnectPppoeUser($sub->username);
        return $ok;
    }

    private function syncQueue($client, Subscription $sub, Service $svc, bool $shouldExist): bool
    {
        $name = 'sub-' . $sub->id;
        $target = $sub->ip_address ? $sub->ip_address . '/32' : null;

        if (! $target) {
            Log::warning('IPoE/static sub missing ip_address', ['sub_id' => $sub->id]);
            return false;
        }

        if ($shouldExist) {
            $rate = $svc->bandwidth_down_kbps . 'k/' . $svc->bandwidth_up_kbps . 'k';
            return $client->addSimpleQueue($name, $target, $rate);
        }

        return $client->removeSimpleQueue($name);
    }
}
