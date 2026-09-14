<?php

use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('challenges', [AuthenticationController::class, 'challenge'])->middleware('throttle:10,1');
    Route::post('sessions', [AuthenticationController::class, 'session'])->middleware('throttle:10,1');

    Route::middleware(['auth.v3.cookie', 'tenant.v3.context'])->group(function (): void {
        Route::get('me', [AuthenticationController::class, 'me']);
        Route::post('switch-enterprise', [AuthenticationController::class, 'switchEnterprise']);
        Route::delete('session', [AuthenticationController::class, 'destroySession']);
    });
});
