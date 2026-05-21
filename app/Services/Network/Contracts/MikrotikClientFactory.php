<?php

namespace App\Services\Network\Contracts;

use App\Models\Router;

interface MikrotikClientFactory
{
    public function forRouter(Router $router): MikrotikClient;
}
