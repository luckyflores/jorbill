<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');


use App\Http\Controllers\Webhooks\SmsWebhookController;

Route::post('/webhooks/sms/{provider}', [SmsWebhookController::class, 'handle'])
    ->where('provider', 'semaphore|globe');

use App\Http\Controllers\Webhooks\HitpayWebhookController;

Route::post('/webhooks/hitpay', [HitpayWebhookController::class, 'handle']);
