<?php

declare(strict_types=1);

use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers\ThirdPartyAvailabilityController;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers\ThirdPartyController;
use App\Context\V3\Modules\Core\ThirdParty\Application\Http\Controllers\ThirdPartyFieldDefinitionController;
use Illuminate\Support\Facades\Route;

Route::prefix('third-parties')->group(function (): void {
    Route::post('/', [ThirdPartyController::class, 'store']);

    Route::get('field-definitions', [ThirdPartyFieldDefinitionController::class, 'index']);
    Route::post('field-definitions', [ThirdPartyFieldDefinitionController::class, 'store']);
    Route::patch('field-definitions/{definition}', [ThirdPartyFieldDefinitionController::class, 'update'])->whereUuid('definition');
    Route::delete('field-definitions/{definition}', [ThirdPartyFieldDefinitionController::class, 'destroy'])->whereUuid('definition');

    Route::get('availability', ThirdPartyAvailabilityController::class);
});
