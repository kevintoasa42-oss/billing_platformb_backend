<?php

use App\Context\V3\Modules\Platform\Infrastructure\Laravel\Http\Controllers\PlatformAdministrationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth.v3.cookie', 'tenant.v3.context'])->group(function (): void {
    Route::get('contexts/{context}', [PlatformAdministrationController::class, 'context'])->where('context', '[A-Za-z0-9_-]+');

    Route::prefix('platform/admin')->group(function (): void {
        Route::get('overview', [PlatformAdministrationController::class, 'overview']);
        Route::get('tenants', [PlatformAdministrationController::class, 'platformTenants']);
        Route::get('contracts', [PlatformAdministrationController::class, 'platformContracts']);
        Route::get('invoices', [PlatformAdministrationController::class, 'platformInvoices']);
        Route::get('support/tickets', [PlatformAdministrationController::class, 'platformSupportTickets']);
        Route::get('users', [PlatformAdministrationController::class, 'platformUsers']);
    });

    Route::get('users', [PlatformAdministrationController::class, 'users']);
    Route::post('users', [PlatformAdministrationController::class, 'createUser']);
    Route::patch('users/{id}', [PlatformAdministrationController::class, 'updateUser'])->whereNumber('id');
    Route::delete('users/{id}', [PlatformAdministrationController::class, 'deleteUser'])->whereNumber('id');

    Route::get('enterprises', [PlatformAdministrationController::class, 'enterprises']);
    Route::post('enterprises', [PlatformAdministrationController::class, 'createEnterprise']);
    Route::get('enterprises/{id}', [PlatformAdministrationController::class, 'enterprise'])->whereNumber('id');
    Route::patch('enterprises/{id}', [PlatformAdministrationController::class, 'updateEnterprise'])->whereNumber('id');
    Route::get('enterprises/{enterprise}/users', [PlatformAdministrationController::class, 'enterpriseUsers'])->whereNumber('enterprise');
    Route::post('enterprises/{enterprise}/users', [PlatformAdministrationController::class, 'assignUser'])->whereNumber('enterprise');
    Route::patch('enterprises/{enterprise}/users/{user}', [PlatformAdministrationController::class, 'updateUserAssignment'])->whereNumber(['enterprise', 'user']);
    Route::delete('enterprises/{enterprise}/users/{user}', [PlatformAdministrationController::class, 'removeUserAssignment'])->whereNumber(['enterprise', 'user']);
    Route::get('enterprises/{enterprise}/tax-settings', [PlatformAdministrationController::class, 'taxSettings'])->whereNumber('enterprise');
    Route::post('enterprises/{enterprise}/tax-settings', [PlatformAdministrationController::class, 'saveTaxSettings'])->whereNumber('enterprise');
    Route::get('enterprises/{enterprise}/electronic-signature', [PlatformAdministrationController::class, 'electronicSignature'])->whereNumber('enterprise');
    Route::post('enterprises/{enterprise}/electronic-signature', [PlatformAdministrationController::class, 'saveElectronicSignature'])->whereNumber('enterprise');
    Route::delete('enterprises/{enterprise}/electronic-signature', [PlatformAdministrationController::class, 'deleteElectronicSignature'])->whereNumber('enterprise');
    Route::get('enterprises/{enterprise}/sri-certification', [PlatformAdministrationController::class, 'sriCertification'])->whereNumber('enterprise');
    Route::post('enterprises/{enterprise}/sri-certification', [PlatformAdministrationController::class, 'startSriCertification'])->whereNumber('enterprise');
    Route::get('enterprises/{enterprise}/sri-certification/{runId}', [PlatformAdministrationController::class, 'sriCertificationRun'])->whereNumber(['enterprise', 'runId']);

    Route::get('countries', [PlatformAdministrationController::class, 'countries']);
    Route::get('provinces', [PlatformAdministrationController::class, 'provinces']);
    Route::get('cities', [PlatformAdministrationController::class, 'cities']);
    Route::get('economic-activities', [PlatformAdministrationController::class, 'economicActivities']);
    Route::get('sri-environments', [PlatformAdministrationController::class, 'sriEnvironments']);

    Route::get('roles', [PlatformAdministrationController::class, 'roles']);
    Route::get('roles/{id}', [PlatformAdministrationController::class, 'role'])->whereNumber('id');
    Route::post('roles', [PlatformAdministrationController::class, 'createRole']);
    Route::patch('roles/{id}', [PlatformAdministrationController::class, 'updateRole'])->whereNumber('id');
    Route::delete('roles/{id}', [PlatformAdministrationController::class, 'deleteRole'])->whereNumber('id');
    Route::post('roles/{id}/menus', [PlatformAdministrationController::class, 'roleMenus'])->whereNumber('id');
    Route::get('menus', [PlatformAdministrationController::class, 'menus']);
    Route::get('menus/tree', [PlatformAdministrationController::class, 'menuTree']);
    Route::post('menus', [PlatformAdministrationController::class, 'createMenu']);
    Route::patch('menus/{id}', [PlatformAdministrationController::class, 'updateMenu'])->whereNumber('id');
    Route::delete('menus/{id}', [PlatformAdministrationController::class, 'deleteMenu'])->whereNumber('id');
    Route::get('permissions', [PlatformAdministrationController::class, 'permissions']);
});
