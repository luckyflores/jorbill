<?php

namespace App\Services\Network\Live;

use App\Models\Olt;
use App\Services\Network\Contracts\OltClient;
use App\Services\Network\Contracts\OltClientFactory;
use App\Services\Network\Null\NullOltClient;

class LiveOltClientFactory implements OltClientFactory
{
    public function forOlt(Olt $olt): OltClient
    {
        return match ($olt->vendor) {
            'zte_cli', 'zte', 'oem_zte' => new ZteCliOltClient(
                host: $olt->ip_address,
                port: $olt->ssh_port,
                user: $olt->ssh_user,
                password: $olt->ssh_password,
                enablePassword: $olt->enable_password,
                promptPattern: $olt->prompt_pattern,
                saveCommand: $olt->save_command,
            ),
            default => new NullOltClient(),
        };
    }
}
