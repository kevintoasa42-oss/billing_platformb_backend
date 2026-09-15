<?php

use App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers\BranchController;
use App\Context\V3\Modules\Core\Establishment\Application\Http\Controllers\IssuancePointController;
use Illuminate\Support\Facades\Route;

/**
 * Branches + Issuance Points — BranchController backed by EstablishmentUseCase.
 * Matches ArtraFiscalBackEnd ConsolidatedWebController surface.
 * Tables: core.establishments (branches), core.emission_points (issuance points),
 * fiscal.sequences (next-sequential).
 */
Route::prefix('branches')->group(function (): void {
    Route::get('/', [BranchController::class, 'index']);
    Route::post('/', [BranchController::class, 'store']);
    Route::patch('/{id}', [BranchController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [BranchController::class, 'destroy'])->whereNumber('id');

    Route::get('/{branch}/issuance-points', [BranchController::class, 'issuancePoints'])->whereNumber('branch');
    Route::post('/{branch}/issuance-points', [BranchController::class, 'createIssuancePoint'])->whereNumber('branch');
    Route::get('/{branch}/issuance-points/{point}/next-sequential', [BranchController::class, 'nextSequential'])->whereNumber(['branch', 'point']);
});

Route::prefix('issuance-points')->group(function (): void {
    Route::patch('/{id}', [IssuancePointController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [IssuancePointController::class, 'destroy'])->whereNumber('id');
});
