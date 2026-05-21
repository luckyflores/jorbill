<?php

return [
    'default' => env('PAYMENT_GATEWAY', 'null'),

    'gateways' => [
        'xendit' => [
            'api_key' => env('XENDIT_API_KEY'),
            'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
        ],
        'paymongo' => [
            'public_key' => env('PAYMONGO_PUBLIC_KEY'),
            'secret_key' => env('PAYMONGO_SECRET_KEY'),
        ],
        'maya' => [
            'public_key' => env('MAYA_PUBLIC_KEY'),
            'secret_key' => env('MAYA_SECRET_KEY'),
        ],
        'dragonpay' => [
            'merchant_id' => env('DRAGONPAY_MERCHANT_ID'),
            'password' => env('DRAGONPAY_PASSWORD'),
        ],
    ],
];
