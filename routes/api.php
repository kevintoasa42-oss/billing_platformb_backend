<?php

use App\Context\V1\Enterprise\Application\Http\Controllers\AuthController;
use App\Context\V1\Enterprise\Application\Http\Controllers\EnterpriseController;
use App\Context\V1\Enterprise\Application\Http\Controllers\UserController;
use App\Context\V1\Menu\Application\Http\Controllers\MenuController;
use App\Context\V1\Product\Application\Http\Controllers\TaxController;
use App\Context\V1\Carrier\Application\Http\Controllers\CarrierController;
use App\Context\V1\Signature\Application\Http\Controllers\SignatureController;
use App\Context\V1\Product\Application\Http\Controllers\ProductController;
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

        // --- Carriers (tenant) ---
        Route::get('/carriers', [CarrierController::class, 'index']);
        Route::get('/carriers/{id}', [CarrierController::class, 'show']);
        Route::post('/carriers', [CarrierController::class, 'store']);
        Route::put('/carriers/{id}', [CarrierController::class, 'update']);
        Route::patch('/carriers/{id}', [CarrierController::class, 'update']);
        Route::patch('/carriers/{id}/status', [CarrierController::class, 'changeStatus']);

        // --- Signatures (tenant) ---
        Route::get('/signatures', [SignatureController::class, 'index']);
        Route::get('/signatures/{id}', [SignatureController::class, 'show']);
        Route::post('/signatures', [SignatureController::class, 'store']);
        Route::put('/signatures/{id}', [SignatureController::class, 'update']);
        Route::patch('/signatures/{id}', [SignatureController::class, 'update']);
        Route::patch('/signatures/{id}/status', [SignatureController::class, 'changeStatus']);
    });

    // --- Ivas (catálogo central, no requiere tenant) ---
    Route::get('/taxes', [TaxController::class, 'index']);
});
