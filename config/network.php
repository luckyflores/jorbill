<?php

return [
    'uisp' => [
        'driver' => env('UISP_DRIVER', 'null'),
        'base_url' => env('UISP_BASE_URL'),
        'api_token' => env('UISP_API_TOKEN'),
    ],
    'genieacs' => [
        'driver' => env('GENIEACS_DRIVER', 'null'),
        'base_url' => env('GENIEACS_BASE_URL'),
        'username' => env('GENIEACS_USERNAME'),
        'password' => env('GENIEACS_PASSWORD'),
    ],
    'mikrotik' => [
        'driver' => env('MIKROTIK_DRIVER', 'null'),
    ],
];
