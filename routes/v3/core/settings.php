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

Route::get('payment-method-settings', [PaymentMethodSettingsController::class, 'index']);
Route::patch('payment-method-settings/{code}', [PaymentMethodSettingsController::class, 'update']);

Route::get('additional-info-presets', [AdditionalInfoPresetController::class, 'index']);
Route::get('additional-info-presets/available', [AdditionalInfoPresetController::class, 'available']);
Route::post('additional-info-presets', [AdditionalInfoPresetController::class, 'store']);
Route::patch('additional-info-presets/{id}', [AdditionalInfoPresetController::class, 'update'])->whereNumber('id');
Route::delete('additional-info-presets/{id}', [AdditionalInfoPresetController::class, 'destroy'])->whereNumber('id');
