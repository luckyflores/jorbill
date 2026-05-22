<?php

return [
    'default'     => env('NOTIFIER', 'log'),        // null | log | semaphore | globe
    'log_channel' => env('NOTIFIER_LOG_CHANNEL', 'stack'),

    'semaphore' => [
        'api_key'     => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'JorBill'),
    ],

    'globe' => [
        'access_token'  => env('GLOBE_ACCESS_TOKEN'),
        'endpoint'      => env('GLOBE_ENDPOINT', 'https://api.m360.globe.com.ph/sms/send'),
        'sender_name'   => env('GLOBE_SENDER_NAME', 'JORBILL'),
        'payload_shape' => env('GLOBE_PAYLOAD_SHAPE', 'm360'),   // 'm360' or 'labs'
        'shortcode'     => env('GLOBE_SHORTCODE'),                // required for 'labs' shape
    ],
];
