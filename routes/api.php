<?php

use App\Contexto\Enterprise\Aplicacion\CasosDeUso\AsignarEmpresaAUsuarioCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\AsignarRolAUsuarioCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\CrearEmpresaCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\CrearUsuarioCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\ListarEmpresasCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\LoginCasoUso;
use App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO;
use App\Contexto\Enterprise\Aplicacion\DTOs\LoginDTO;
use App\Contexto\Enterprise\Aplicacion\DTOs\UsuarioDTO;
use App\Contexto\Menu\Aplicacion\CasosDeUso\AsignarMenuARolCasoUso;
use App\Contexto\Menu\Aplicacion\CasosDeUso\CrearMenuCasoUso;
use App\Contexto\Menu\Aplicacion\CasosDeUso\ListarMenusCasoUso;
use App\Contexto\Menu\Aplicacion\CasosDeUso\ObtenerMenusPorRolCasoUso;
use App\Contexto\Menu\Aplicacion\DTOs\MenuDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rutas del API con arquitectura DDD. Los casos de uso se inyectan
| directamente en las closures (resueltos via ContextoServiceProvider).
|
*/

// --- Autenticacion ---
Route::post('/login', function (Request $request, LoginCasoUso $casoUso) {
    $dto = LoginDTO::fromArray($request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
        'enterprise_id' => 'required|integer',
    ]));

    try {
        return response()->json($casoUso->ejecutar($dto));
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 401);
    }
});

// --- Rutas protegidas (auth:sanctum) ---
Route::middleware('auth:sanctum')->group(function () {

    // Usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Empresas ---
    Route::post('/empresas', function (Request $request, CrearEmpresaCasoUso $casoUso) {
        $dto = EmpresaDTO::fromArray($request->validate([
            'nombre' => 'required|string',
            'ruc' => 'required|string|max:13|unique:enterprises,ruc',
            'tradename' => 'required|string',
            'matrixname' => 'required|string',
            'telefono' => 'required|string',
            'correo_corporativo' => 'required|email',
        ]));

        return response()->json($casoUso->ejecutar($dto), 201);
    });

    Route::get('/empresas', function (ListarEmpresasCasoUso $casoUso) {
        return response()->json($casoUso->ejecutar());
    });

    // --- Usuarios ---
    Route::post('/usuarios', function (Request $request, CrearUsuarioCasoUso $casoUso) {
        $dto = UsuarioDTO::fromArray($request->validate([
            'nombre' => 'required|string',
            'email' => 'required|email|unique:usuarios,email',
            'password' => 'required|string|min:8',
        ]));

        return response()->json($casoUso->ejecutar($dto), 201);
    });

    Route::post('/usuarios/{id}/roles', function (int $id, Request $request, AsignarRolAUsuarioCasoUso $casoUso) {
        $data = $request->validate([
            'rol_id' => 'required|integer|exists:roles,id',
        ]);

        $casoUso->ejecutar($id, $data['rol_id']);

        return response()->json(['message' => 'Rol asignado correctamente.']);
    });

    Route::post('/usuarios/{id}/empresas', function (int $id, Request $request, AsignarEmpresaAUsuarioCasoUso $casoUso) {
        $data = $request->validate([
            'enterprise_id' => 'required|integer|exists:enterprises,id',
        ]);

        $casoUso->ejecutar($id, $data['enterprise_id']);

        return response()->json(['message' => 'Empresa asignada correctamente.']);
    });

    // --- Menus ---
    Route::post('/menus', function (Request $request, CrearMenuCasoUso $casoUso) {
        $dto = MenuDTO::fromArray($request->validate([
            'nombre' => 'required|string',
            'ruta' => 'nullable|string',
            'icono' => 'nullable|string',
            'parent_id' => 'nullable|integer|exists:menus,id',
            'orden' => 'nullable|integer',
        ]));

        return response()->json($casoUso->ejecutar($dto), 201);
    });

    Route::get('/menus', function (ListarMenusCasoUso $casoUso) {
        return response()->json($casoUso->ejecutar());
    });

    Route::post('/menus/{id}/roles', function (int $id, Request $request, AsignarMenuARolCasoUso $casoUso) {
        $data = $request->validate([
            'rol_id' => 'required|integer|exists:roles,id',
        ]);

        $casoUso->ejecutar($id, $data['rol_id']);

        return response()->json(['message' => 'Menu asignado al rol correctamente.']);
    });

    // --- Obtener menus por rol del usuario autenticado ---
    Route::get('/menus/por-rol', function (Request $request, ObtenerMenusPorRolCasoUso $casoUso) {
        $usuarioId = $request->user()->id;

        return response()->json($casoUso->ejecutar($usuarioId));
    })->middleware('tenant');
});
