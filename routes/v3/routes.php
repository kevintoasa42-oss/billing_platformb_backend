<?php

use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

/**
 * V3 Authentication — cookie/bearer session against auth.sessions.
 */
Route::prefix('auth')->group(function (): void {
    Route::post('challenges', [AuthenticationController::class, 'challenge'])->middleware('throttle:10,1');
    Route::post('sessions', [AuthenticationController::class, 'session'])->middleware('throttle:10,1');

    Route::middleware(['auth.v3.cookie', 'tenant.v3.context'])->group(function (): void {
        Route::get('me', [AuthenticationController::class, 'me']);
        Route::post('refresh', [AuthenticationController::class, 'refresh']);
        Route::post('switch-enterprise', [AuthenticationController::class, 'switchEnterprise']);
        Route::delete('session', [AuthenticationController::class, 'destroySession']);
        Route::get('sessions', [AuthenticationController::class, 'activeSessions']);
        Route::delete('sessions', [AuthenticationController::class, 'revokeOtherSessions']);
        Route::delete('sessions/{id}', [AuthenticationController::class, 'revokeActiveSession'])->whereNumber('id');
        Route::post('verify-password', [AuthenticationController::class, 'verifyPassword']);
        Route::post('change-password', [AuthenticationController::class, 'changePassword']);
        Route::get('mfa', [AuthenticationController::class, 'mfaStatus']);
        Route::post('mfa', [AuthenticationController::class, 'beginMfa']);
        Route::post('mfa/confirm', [AuthenticationController::class, 'confirmMfa']);
        Route::get('preferences', [AuthenticationController::class, 'preferences']);
        Route::patch('preferences', [AuthenticationController::class, 'savePreferences']);
        Route::post('support/users/{id}/password-reset', [AuthenticationController::class, 'supportPasswordReset'])->whereNumber('id');
    });
});

/**
 * V3 Core — bounded context for carrier, establishment, third-party,
 * product, vehicle, and economic activity management.
 *
 * All routes require auth.v3.cookie + tenant.v3.context middleware.
 */
Route::prefix('core')
    ->middleware(['auth.v3.cookie', 'tenant.v3.context'])
    ->group(function (): void {
        // Carrier establishments + emission points (CarrierEstablishmentController, CarrierEmissionPointController)
        Route::group([], base_path('routes/v3/core/carrier_establishments.php'));

        // Carrier affiliations + vehicle assignments (CarrierAffiliationController)
        Route::group([], base_path('routes/v3/core/carrier_affiliations.php'));

        // Companies (CompanyController) — core.companies, one per tenant
        Route::group([], base_path('routes/v3/core/companies.php'));

        // Branches + issuance points (BranchController, IssuancePointController) — legacy_id surface
        Route::group([], base_path('routes/v3/core/branches.php'));

        // Vehicles (VehicleController) — core.vehicles, tenant-scoped unique plate
        Route::group([], base_path('routes/v3/core/vehicles.php'));

        // Economic activities (EconomicActivityController) — global catalog, no RLS
        Route::group([], base_path('routes/v3/core/economic_activities.php'));

        // Notifications (NotificationController) — email via Mail facade
        Route::group([], base_path('routes/v3/core/notifications.php'));

        // SRI IVA types + percentages (SriIvaTypeController, SriIvaPercentageController)
        Route::group([], base_path('routes/v3/core/sri_iva_types.php'));

        // Products + product settings + product taxes (ProductController, ProductSettingsController, ProductTaxController)
        Route::group([], base_path('routes/v3/core/products.php'));

        // Settings — customer settings, payment method settings, additional info presets
        Route::group([], base_path('routes/v3/core/settings.php'));
    });

Route::group([], base_path('routes/v3/platform.php'));
