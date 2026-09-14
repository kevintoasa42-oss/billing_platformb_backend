<?php

use App\Context\V3\Modules\Core\Vehicle\Application\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

/**
 * Vehicles — VehicleController backed by VehicleUseCase + VehicleRepository.
 * Table: core.vehicles (RLS enabled, tenant-scoped unique plate).
 */
Route::prefix('vehicles')->group(function (): void {
    Route::get('/', [VehicleController::class, 'index']);
    Route::post('/', [VehicleController::class, 'store']);
    Route::get('/{id}', [VehicleController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [VehicleController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');
});
