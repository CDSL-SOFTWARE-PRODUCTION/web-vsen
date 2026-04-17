<?php

use App\Http\Controllers\Api\DriverDeliveryController;
use App\Http\Middleware\VerifyOpsDriverToken;
use Illuminate\Support\Facades\Route;

Route::middleware([VerifyOpsDriverToken::class])->group(function (): void {
    Route::get('/ops/driver/deliveries/{delivery}', [DriverDeliveryController::class, 'show']);
    Route::post('/ops/driver/deliveries/{delivery}/mark-delivered', [DriverDeliveryController::class, 'markDelivered']);
});
