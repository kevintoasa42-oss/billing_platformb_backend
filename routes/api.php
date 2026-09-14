<?php

use App\Context\V1\Modules\BranchOffices\Infrastructure\Laravel\Http\Controllers\BranchOfficeController;
use App\Context\V1\Modules\Carrier\Application\Http\Controllers\CarrierController;
use App\Context\V1\Modules\Clients\Infrastructure\Laravel\Http\Controllers\ClientController;
use App\Context\V1\Modules\EmissionPoints\Infrastructure\Laravel\Http\Controllers\EmissionPointController;
use App\Context\V1\Modules\Enterprise\Application\Http\Controllers\AuthController;
use App\Context\V1\Modules\Enterprise\Application\Http\Controllers\EnterpriseController;
use App\Context\V1\Modules\Enterprise\Application\Http\Controllers\UserController;
use App\Context\V1\Modules\Invoice\Application\Http\Controllers\InvoiceController;
use App\Context\V1\Modules\Invoice\Application\Http\Controllers\PaymentMethodController;
use App\Context\V1\Modules\Menu\Application\Http\Controllers\MenuController;
use App\Context\V1\Modules\Product\Application\Http\Controllers\ProductController;
use App\Context\V1\Modules\Product\Application\Http\Controllers\TaxController;
use App\Context\V1\Modules\Signature\Application\Http\Controllers\EnterpriseSignatureController;
use App\Context\V1\Modules\Signature\Application\Http\Controllers\SignatureController;
use App\Context\V1\Modules\SriAuthorization\Application\Http\Controllers\SriAuthorizationController;
use App\Context\V1\Modules\SriVoucherTypes\Infrastructure\Laravel\Http\Controllers\SriVoucherTypeController;
use App\Context\V1\Modules\XmlGeneration\Application\Http\Controllers\InvoiceXmlController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas del API con arquitectura DDD. Cada contexto expone sus controladores
| que delegan a los casos de uso correspondientes.
|
*/

Route::prefix('v1')->group(base_path('routes/v1/routes.php'));
Route::prefix('v3')->group(base_path('routes/v3/routes.php'));

// --- Autenticacion (sin auth) ---
Route::post('/auth/login', [AuthController::class, 'loginInicial']);
Route::post('/login', [AuthController::class, 'login']);

// --- Rutas protegidas (auth:sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // User autenticado
    Route::get('/user', [AuthController::class, 'user']);

    // --- Enterprises ---
    Route::post('/enterprises', [EnterpriseController::class, 'store']);
    Route::get('/enterprises', [EnterpriseController::class, 'index']);

    // --- Users ---
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/users/{id}/roles', [UserController::class, 'asignarRol']);
    Route::post('/users/{id}/enterprises', [UserController::class, 'asignarEnterprise']);

    // --- Menus ---
    Route::post('/menus', [MenuController::class, 'store']);
    Route::get('/menus', [MenuController::class, 'index']);
    Route::post('/menus/{id}/roles', [MenuController::class, 'asignarRol']);

    // --- Obtener menus por rol del user autenticado ---
    Route::get('/menus/by-role', [MenuController::class, 'menusByRole'])
        ->middleware('tenant');

    // --- Products (requiere tenant) ---
    Route::middleware('tenant')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::patch('/products/{id}', [ProductController::class, 'update']);
        Route::patch('/products/{id}/status', [ProductController::class, 'changeStatus']);

        // --- Branch offices (tenant) ---
        Route::get('/branch-offices', [BranchOfficeController::class, 'index']);
        Route::get('/branch-offices/{id}', [BranchOfficeController::class, 'show']);
        Route::post('/branch-offices', [BranchOfficeController::class, 'store']);
        Route::put('/branch-offices/{id}', [BranchOfficeController::class, 'update']);
        Route::patch('/branch-offices/{id}', [BranchOfficeController::class, 'update']);
        Route::delete('/branch-offices/{id}', [BranchOfficeController::class, 'destroy']);

        // --- Clients (tenant) ---
        Route::get('/clients', [ClientController::class, 'index']);
        Route::get('/clients/{id}', [ClientController::class, 'show']);
        Route::post('/clients', [ClientController::class, 'store']);
        Route::put('/clients/{id}', [ClientController::class, 'update']);
        Route::patch('/clients/{id}', [ClientController::class, 'update']);
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']);

        // --- Emission points (tenant) ---
        // Declare this static route before /emission-points/{id}.
        Route::get('/emission-points/next-sequential', [EmissionPointController::class, 'nextSequential']);
        Route::post('/emission-points/next-sequential/take', [EmissionPointController::class, 'takeNextSequential']);
        Route::get('/emission-points', [EmissionPointController::class, 'index']);
        Route::get('/emission-points/{id}', [EmissionPointController::class, 'show']);
        Route::post('/emission-points', [EmissionPointController::class, 'store']);
        Route::put('/emission-points/{id}', [EmissionPointController::class, 'update']);
        Route::patch('/emission-points/{id}', [EmissionPointController::class, 'update']);
        Route::delete('/emission-points/{id}', [EmissionPointController::class, 'destroy']);

        // --- Carriers (tenant) ---
        Route::get('/carriers', [CarrierController::class, 'index']);
        Route::get('/carriers/{id}', [CarrierController::class, 'show']);
        Route::post('/carriers', [CarrierController::class, 'store']);
        Route::put('/carriers/{id}', [CarrierController::class, 'update']);
        Route::patch('/carriers/{id}', [CarrierController::class, 'update']);
        Route::patch('/carriers/{id}/status', [CarrierController::class, 'changeStatus']);

        // --- Signatures (tenant) ---
        Route::get('/signatures/{id}', [SignatureController::class, 'show']);
        Route::post('/signatures', [SignatureController::class, 'store']);
        Route::put('/signatures/{id}', [SignatureController::class, 'update']);
        Route::patch('/signatures/{id}', [SignatureController::class, 'update']);
        Route::patch('/signatures/{id}/status', [SignatureController::class, 'changeStatus']);

        // --- Invoices (tenant) ---
        Route::get('/invoices', [InvoiceController::class, 'index']);
        Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
        Route::post('/invoices', [InvoiceController::class, 'store']);
        Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
        Route::patch('/invoices/{id}', [InvoiceController::class, 'update']);
        Route::patch('/invoices/{id}/status', [InvoiceController::class, 'changeStatus']);
        Route::patch('/invoices/{id}/void', [InvoiceController::class, 'void']);
        Route::get('/invoices/{id}/xml', [InvoiceXmlController::class, 'show']);
        Route::post('/invoices/{id}/authorize', [SriAuthorizationController::class, 'authorize']);
        Route::get('/invoices/{id}/sri-logs', [SriAuthorizationController::class, 'logs']);
    });

    // --- Enterprise Signature (central DB, uses enterprise_id from token) ---
    Route::get('/enterprise-signature', [EnterpriseSignatureController::class, 'show']);
    Route::post('/enterprise-signature', [EnterpriseSignatureController::class, 'store']);
    Route::put('/enterprise-signature', [EnterpriseSignatureController::class, 'update']);
    Route::patch('/enterprise-signature', [EnterpriseSignatureController::class, 'update']);
    Route::patch('/enterprise-signature/status', [EnterpriseSignatureController::class, 'changeStatus']);

    // --- Ivas (catálogo central, no requiere tenant) ---
    Route::get('/taxes', [TaxController::class, 'index']);

    // --- Payment methods (catálogo central, no requiere tenant) ---
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);

    // --- SRI voucher types (catálogo central, no requiere tenant) ---
    Route::get('/sri-voucher-types', [SriVoucherTypeController::class, 'index']);
    Route::get('/sri-voucher-types/{id}', [SriVoucherTypeController::class, 'show']);
    Route::post('/sri-voucher-types', [SriVoucherTypeController::class, 'store']);
    Route::put('/sri-voucher-types/{id}', [SriVoucherTypeController::class, 'update']);
    Route::patch('/sri-voucher-types/{id}', [SriVoucherTypeController::class, 'update']);
    Route::delete('/sri-voucher-types/{id}', [SriVoucherTypeController::class, 'destroy']);
});
