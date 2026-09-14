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
Route::get('branches', [BranchController::class, 'index']);
Route::post('branches', [BranchController::class, 'store']);
Route::patch('branches/{id}', [BranchController::class, 'update'])->whereNumber('id');
Route::delete('branches/{id}', [BranchController::class, 'destroy'])->whereNumber('id');
Route::get('branches/{branch}/issuance-points', [BranchController::class, 'issuancePoints'])->whereNumber('branch');
Route::post('branches/{branch}/issuance-points', [BranchController::class, 'createIssuancePoint'])->whereNumber('branch');
Route::get('branches/{branch}/issuance-points/{point}/next-sequential', [BranchController::class, 'nextSequential'])->whereNumber(['branch', 'point']);
Route::patch('issuance-points/{id}', [IssuancePointController::class, 'update'])->whereNumber('id');
Route::delete('issuance-points/{id}', [IssuancePointController::class, 'destroy'])->whereNumber('id');
