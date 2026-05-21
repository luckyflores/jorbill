<?php

namespace App\Services\Network\Live;

use App\Models\Router;
use App\Services\Network\Contracts\MikrotikClient;
use App\Services\Network\Contracts\MikrotikClientFactory;

class LiveMikrotikClientFactory implements MikrotikClientFactory
{
    public function forRouter(Router $router): MikrotikClient
    {
        return new LiveMikrotikClient(
            host: $router->ip_address,
            port: $router->api_port ?? 8728,
            user: $router->api_user,
            password: $router->api_password,  // encrypted cast decrypts automatically
        );
    }
}
