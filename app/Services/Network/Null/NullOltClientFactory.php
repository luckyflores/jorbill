<?php

namespace App\Services\Network\Null;

use App\Models\Olt;
use App\Services\Network\Contracts\OltClient;
use App\Services\Network\Contracts\OltClientFactory;

class NullOltClientFactory implements OltClientFactory
{
    public function forOlt(Olt $olt): OltClient
    {
        return new NullOltClient();
    }
}
