<?php

use App\Context\V3\Modules\Core\Settings\Application\Http\Controllers\AdditionalInfoPresetController;
use App\Context\V3\Modules\Core\Settings\Application\Http\Controllers\CustomerSettingsController;
use App\Context\V3\Modules\Core\Settings\Application\Http\Controllers\PaymentMethodSettingsController;
use Illuminate\Support\Facades\Route;

/**
 * Settings — customer settings, payment method settings, additional info presets.
 * Matches ArtraFiscalBackEnd ConsolidatedWebController surface.
 * Tables: core.tenant_settings (customer_settings, payment_method_settings jsonb),
 * fiscal.additional_info_presets (RLS enabled, legacy_id bigint IDENTITY).
 */
Route::patch('customer-settings', [CustomerSettingsController::class, 'update']);

Route::prefix('payment-method-settings')->group(function (): void {
    Route::get('/', [PaymentMethodSettingsController::class, 'index']);
    Route::patch('/{code}', [PaymentMethodSettingsController::class, 'update']);
});

Route::prefix('additional-info-presets')->group(function (): void {
    Route::get('/', [AdditionalInfoPresetController::class, 'index']);
    Route::get('/available', [AdditionalInfoPresetController::class, 'available']);
    Route::post('/', [AdditionalInfoPresetController::class, 'store']);
    Route::patch('/{id}', [AdditionalInfoPresetController::class, 'update'])->whereNumber('id');
    Route::delete('/{id}', [AdditionalInfoPresetController::class, 'destroy'])->whereNumber('id');
});
