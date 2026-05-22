<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


use App\Http\Controllers\Webhooks\SmsWebhookController;

Route::post('/webhooks/sms/{provider}', [SmsWebhookController::class, 'handle'])
    ->where('provider', 'semaphore|globe');
