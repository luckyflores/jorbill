<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'null'),

    'gateways' => [
        'hitpay' => [
            'api_key'      => env('HITPAY_API_KEY'),
            'salt'         => env('HITPAY_SALT'),
            'use_live'     => env('HITPAY_USE_LIVE', false),
            'currency'     => env('HITPAY_CURRENCY', 'PHP'),
            'webhook_url'  => env('APP_URL', 'https://jorbill.maltixtech.xyz') . '/webhooks/hitpay',
            'redirect_url' => env('APP_URL', 'https://jorbill.maltixtech.xyz') . '/admin/payments',
        ],
    ],
];
