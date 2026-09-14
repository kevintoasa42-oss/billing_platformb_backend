<?php

use App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers\EmissionPointController;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers\EstablishmentController;
use Illuminate\Support\Facades\Route;

/**
 * Establishments — EstablishmentController backed by EstablishmentUseCase + EstablishmentRepository.
 * Table: core.establishments (RLS enabled, references core.companies).
 * Includes activity sync via core.establishment_activities.
 */
Route::prefix('establishments')->group(function (): void {
    Route::get('/', [EstablishmentController::class, 'index']);
    Route::post('/', [EstablishmentController::class, 'store']);
    Route::get('/{id}', [EstablishmentController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [EstablishmentController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');

    // Emission points belonging to a specific establishment.
    Route::get('/{id}/emission-points', [EmissionPointController::class, 'byEstablishment'])->where('id', '[0-9a-fA-F-]{36}');
});

/**
 * Emission points — EmissionPointController backed by EmissionPointUseCase + EmissionPointRepository.
 * Table: core.emission_points (RLS enabled, references core.establishments).
 */
Route::prefix('emission-points')->group(function (): void {
    Route::get('/', [EmissionPointController::class, 'index']);
    Route::post('/', [EmissionPointController::class, 'store']);
    Route::get('/{id}', [EmissionPointController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [EmissionPointController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');
});
