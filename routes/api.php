<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\DiagnosticsController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/token', [AuthController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/diagnostics', [DiagnosticsController::class, 'store']);
    Route::get('/diagnostics/mine', [DiagnosticsController::class, 'mine']);

    // Tech-only
    Route::middleware('role:tech,admin')->group(function () {
        Route::get('/customers/search', [CustomersController::class, 'search']);
        Route::get('/customers/{customer}', [CustomersController::class, 'show']);
        Route::get('/diagnostics/customer/{customer}', [DiagnosticsController::class, 'forCustomer']);
    });
});

// /api/config/diagnostics — public, returns ping targets + speedtest URL so the
// app can fetch defaults at startup without baking them into the build
Route::get('/config/diagnostics', function () {
    return response()->json([
        'fourleaf_gateway'  => \App\Models\Setting::get('diagnostics.fourleaf_gateway', '8.8.8.8'),
        'ping_targets'      => array_map('trim', explode(',',
            \App\Models\Setting::get('diagnostics.ping_targets',
                'google.com, facebook.com, youtube.com, cloudflare.com'))),
        'speedtest_url'     => \App\Models\Setting::get('diagnostics.speedtest_url',
            'https://speedtest.fourleafmedia.com'),
    ]);
});
