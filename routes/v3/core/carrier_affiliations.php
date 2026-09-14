<?php

use App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers\CarrierAffiliationController;
use Illuminate\Support\Facades\Route;

/**
 * Carrier affiliations (third-party + validity range + vehicle assignments).
 *
 * Group: /carrier-affiliations
 * Controller: CarrierAffiliationController
 * UseCase: CarrierAffiliationUseCase
 * Tables: core.carrier_affiliations, core.carrier_vehicle_assignments
 *
 *   GET    /carrier-affiliations          index   — List affiliations
 *   POST   /carrier-affiliations          store   — Create affiliation
 *   GET    /carrier-affiliations/{id}     show    — Show affiliation
 *   PATCH  /carrier-affiliations/{id}     update  — Update affiliation (validity, vehicle assignments)
 */
Route::prefix('carrier-affiliations')->group(function (): void {
    Route::get('/', [CarrierAffiliationController::class, 'index']);
    Route::post('/', [CarrierAffiliationController::class, 'store']);
    Route::get('/{id}', [CarrierAffiliationController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [CarrierAffiliationController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');
});
