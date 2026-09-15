<?php

declare(strict_types=1);

use App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers\CarrierController;
use Illuminate\Support\Facades\Route;

Route::prefix('carriers')->group(function (): void {
    Route::get('/', [CarrierController::class, 'index']);
    Route::post('/onboard', [CarrierController::class, 'store']);
    Route::get('/{id}', [CarrierController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [CarrierController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}/payment-account', [CarrierController::class, 'paymentAccount'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}/signature', [CarrierController::class, 'signature'])->where('id', '[0-9a-fA-F-]{36}');
    Route::post('/{id}/documents', [CarrierController::class, 'document'])->where('id', '[0-9a-fA-F-]{36}');
    Route::delete('/{id}/documents/{documentId}', [CarrierController::class, 'cancelDocument'])->where('id', '[0-9a-fA-F-]{36}')->where('documentId', '[0-9a-fA-F-]{36}');
    Route::post('/{id}/allocations', [CarrierController::class, 'allocate'])->where('id', '[0-9a-fA-F-]{36}');
    Route::delete('/{id}/allocations/{allocationId}', [CarrierController::class, 'reverseAllocation'])->where('id', '[0-9a-fA-F-]{36}')->where('allocationId', '[0-9a-fA-F-]{36}');
    Route::post('/{id}/operations/{operationId}/settlement-review/clear', [CarrierController::class, 'clearReview'])->where('id', '[0-9a-fA-F-]{36}')->where('operationId', '[0-9a-fA-F-]{36}');
    Route::get('/{id}/audit', [CarrierController::class, 'audit'])->where('id', '[0-9a-fA-F-]{36}');
});
