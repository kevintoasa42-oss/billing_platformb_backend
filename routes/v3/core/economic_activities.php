<?php

use App\Context\V3\Modules\Core\EconomicActivity\Application\Http\Controllers\EconomicActivityController;
use Illuminate\Support\Facades\Route;

/**
 * Economic activities — EconomicActivityController backed by EconomicActivityUseCase + EconomicActivityRepository.
 * Table: core.economic_activities (global catalog, no RLS, no tenant_id).
 * catalog_version CHECK constraint: 'synthetic-lab-v1' or 'staging-legacy-v1'.
 */
Route::prefix('economic-activities')->group(function (): void {
    Route::get('/', [EconomicActivityController::class, 'index']);
    Route::post('/', [EconomicActivityController::class, 'store']);
    Route::get('/{id}', [EconomicActivityController::class, 'show'])->where('id', '[0-9a-zA-Z_-]+');
    Route::patch('/{id}', [EconomicActivityController::class, 'update'])->where('id', '[0-9a-zA-Z_-]+');
});
