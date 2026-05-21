<?php

return [
    'default' => env('NOTIFIER', 'log'),
    'log_channel' => env('NOTIFIER_LOG_CHANNEL', 'stack'),
    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'JorBill'),
    ],
];
