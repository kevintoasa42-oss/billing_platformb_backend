<?php

use App\Context\V3\Modules\Core\SriIva\Application\Http\Controllers\SriIvaPercentageController;
use App\Context\V3\Modules\Core\SriIva\Application\Http\Controllers\SriIvaTypeController;
use Illuminate\Support\Facades\Route;

/**
 * SRI IVA types — SriIvaTypeController backed by SriIvaTypeUseCase + SriIvaTypeRepository.
 * Table: core.sri_iva_types (RLS enabled, tenant-bound catalog of IVA types).
 */
Route::get('sri-iva-types', [SriIvaTypeController::class, 'index']);
Route::post('sri-iva-types', [SriIvaTypeController::class, 'store']);
Route::patch('sri-iva-types/{id}', [SriIvaTypeController::class, 'update'])->whereNumber('id');
Route::delete('sri-iva-types/{id}', [SriIvaTypeController::class, 'destroy'])->whereNumber('id');

/**
 * SRI IVA percentages — nested under a type for list/create, flat for update/delete.
 * Table: core.sri_iva_percentages (RLS enabled, references core.sri_iva_types).
 */
Route::get('sri-iva-types/{type}/percentages', [SriIvaTypeController::class, 'percentages'])->whereNumber('type');
Route::post('sri-iva-types/{type}/percentages', [SriIvaTypeController::class, 'storePercentage'])->whereNumber('type');
Route::patch('sri-iva-percentages/{id}', [SriIvaPercentageController::class, 'update'])->whereNumber('id');
Route::delete('sri-iva-percentages/{id}', [SriIvaPercentageController::class, 'destroy'])->whereNumber('id');
