<?php

declare(strict_types=1);

use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers\ThirdPartyController;
use Illuminate\Support\Facades\Route;

Route::prefix('third-parties')->group(function (): void {
    Route::get('/', [ThirdPartyController::class, 'index']);
    Route::get('/roles', [ThirdPartyController::class, 'roles']);
    Route::get('/availability', [ThirdPartyController::class, 'availability']);
    Route::get('/field-definitions', [ThirdPartyController::class, 'fieldDefinitions']);
    Route::post('/field-definitions', [ThirdPartyController::class, 'createFieldDefinition']);
    Route::patch('/field-definitions/{definition}', [ThirdPartyController::class, 'updateFieldDefinition'])->where('definition', '[0-9a-fA-F-]{36}');
    Route::delete('/field-definitions/{definition}', [ThirdPartyController::class, 'deactivateFieldDefinition'])->where('definition', '[0-9a-fA-F-]{36}');
    Route::get('/customers', [ThirdPartyController::class, 'customers']);
    Route::get('/carriers', [ThirdPartyController::class, 'carriers']);
    Route::post('/', [ThirdPartyController::class, 'store']);
    Route::get('/{id}', [ThirdPartyController::class, 'show'])->where('id', '[0-9a-fA-F-]{36}');
    Route::get('/{id}/customer', [ThirdPartyController::class, 'showCustomer'])->where('id', '[0-9a-fA-F-]{36}');
    Route::get('/{id}/fields', [ThirdPartyController::class, 'fields'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}/fields', [ThirdPartyController::class, 'updateFields'])->where('id', '[0-9a-fA-F-]{36}');
    Route::patch('/{id}', [ThirdPartyController::class, 'update'])->where('id', '[0-9a-fA-F-]{36}');
});
