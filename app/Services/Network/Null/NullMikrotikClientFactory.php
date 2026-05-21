<?php

namespace App\Services\Network\Null;

use App\Models\Router;
use App\Services\Network\Contracts\MikrotikClient;
use App\Services\Network\Contracts\MikrotikClientFactory;

class NullMikrotikClientFactory implements MikrotikClientFactory
{
    public function forRouter(Router $router): MikrotikClient
    {
        return new NullMikrotikClient();
    }
}
