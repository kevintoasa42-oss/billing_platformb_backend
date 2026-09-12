<?php

use App\Contexto\Enterprise\Aplicacion\Http\Controllers\AuthController;
use App\Contexto\Enterprise\Aplicacion\Http\Controllers\EmpresaController;
use App\Contexto\Enterprise\Aplicacion\Http\Controllers\UsuarioController;
use App\Contexto\Menu\Aplicacion\Http\Controllers\MenuController;
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

    // Usuario autenticado
    Route::get('/user', [AuthController::class, 'user']);

    // --- Empresas ---
    Route::post('/empresas', [EmpresaController::class, 'store']);
    Route::get('/empresas', [EmpresaController::class, 'index']);

    // --- Usuarios ---
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::post('/usuarios/{id}/roles', [UsuarioController::class, 'asignarRol']);
    Route::post('/usuarios/{id}/empresas', [UsuarioController::class, 'asignarEmpresa']);

    // --- Menus ---
    Route::post('/menus', [MenuController::class, 'store']);
    Route::get('/menus', [MenuController::class, 'index']);
    Route::post('/menus/{id}/roles', [MenuController::class, 'asignarRol']);

    // --- Obtener menus por rol del usuario autenticado ---
    Route::get('/menus/por-rol', [MenuController::class, 'menusPorRol'])
        ->middleware('tenant');
});
