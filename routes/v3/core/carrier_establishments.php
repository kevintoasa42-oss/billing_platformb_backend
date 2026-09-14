<?php

use App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers\CarrierEmissionPointController;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers\CarrierEstablishmentController;
use Illuminate\Support\Facades\Route;

/**
 * Carrier establishments and emission points.
 *
 * Group: /carrier-establishments
 * Controller: CarrierEstablishmentController
 * UseCase: CarrierEstablishmentUseCase
 * Table: core.carrier_establishments
 *
 *   GET    /carrier-establishments                   index  — List establishments
 *   POST   /carrier-establishments                   store  — Create establishment
 *   GET    /carrier-establishments/{id}              show   — Show establishment
 *   GET    /carrier-establishments/{id}/emission-points  byEstablishment — List emission points for an establishment
 *
 * Group: /carrier-emission-points
 * Controller: CarrierEmissionPointController
 * UseCase: CarrierEmissionPointUseCase
 * Table: core.carrier_emission_points
 *
 *   GET    /carrier-emission-points                  index  — List emission points
 *   POST   /carrier-emission-points                  store  — Create emission point
 *   GET    /carrier-emission-points/{id}             show   — Show emission point
 */
Route::prefix('carrier-establishments')->group(function (): void {
    Route::get('/', [CarrierEstablishmentController::class, 'index']);
    Route::post('/', [CarrierEstablishmentController::class, 'store']);
    Route::get('/{id}', [CarrierEstablishmentController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::get('/{id}/emission-points', [CarrierEmissionPointController::class, 'byEstablishment'])->where('id', '[0-9a-fA-F-]{36}');
});

Route::prefix('carrier-emission-points')->group(function (): void {
    Route::get('/', [CarrierEmissionPointController::class, 'index']);
    Route::post('/', [CarrierEmissionPointController::class, 'store']);
    Route::get('/{id}', [CarrierEmissionPointController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
});
