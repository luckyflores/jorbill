<?php

namespace App\Services\Network\Contracts;

use App\Models\Olt;

interface OltClientFactory
{
    public function forOlt(Olt $olt): OltClient;
}
