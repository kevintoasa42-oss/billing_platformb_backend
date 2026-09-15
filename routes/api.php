<?php

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
