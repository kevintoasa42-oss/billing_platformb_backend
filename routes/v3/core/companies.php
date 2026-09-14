<?php

use App\Context\V3\Modules\Core\Company\Application\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

/**
 * Companies — CompanyController backed by CompanyUseCase + CompanyRepository.
 * Table: core.companies (RLS enabled, one company per tenant).
 */
Route::prefix('companies')->group(function (): void {
    Route::get('/', [CompanyController::class, 'index']);
    Route::post('/', [CompanyController::class, 'store']);
    Route::get('/{id}', [CompanyController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [CompanyController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');
});
