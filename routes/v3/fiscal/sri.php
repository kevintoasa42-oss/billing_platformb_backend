<?php

declare(strict_types=1);

use App\Context\V3\Modules\Fiscal\Sri\Application\Http\Controllers\SriController;
use Illuminate\Support\Facades\Route;

/**
 * V3 Fiscal SRI — admin routes for SRI mode and dispatch control.
 */
Route::prefix('admin')->group(function (): void {
    Route::patch('fiscal-sri-mode', [SriController::class, 'updateMode']);
    Route::get('fiscal-dispatch-control', [SriController::class, 'dispatchControl']);
    Route::patch('fiscal-dispatch-control', [SriController::class, 'updateDispatchControl']);
});
