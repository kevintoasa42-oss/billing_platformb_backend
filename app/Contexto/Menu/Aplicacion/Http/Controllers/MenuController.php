<?php

namespace App\Contexto\Menu\Aplicacion\Http\Controllers;

use App\Contexto\Menu\Aplicacion\CasosDeUso\AsignarMenuARolCasoUso;
use App\Contexto\Menu\Aplicacion\CasosDeUso\CrearMenuCasoUso;
use App\Contexto\Menu\Aplicacion\CasosDeUso\ListarMenusCasoUso;
use App\Contexto\Menu\Aplicacion\CasosDeUso\ObtenerMenusPorRolCasoUso;
use App\Contexto\Menu\Aplicacion\Http\Requests\AsignarMenuRolRequest;
use App\Contexto\Menu\Aplicacion\Http\Requests\CrearMenuRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private CrearMenuCasoUso $crearMenuCasoUso,
        private ListarMenusCasoUso $listarMenusCasoUso,
        private AsignarMenuARolCasoUso $asignarMenuRolCasoUso,
        private ObtenerMenusPorRolCasoUso $obtenerMenusPorRolCasoUso,
    ) {}

    /**
     * POST /api/menus
     * Crea un menu.
     */
    public function store(CrearMenuRequest $request): JsonResponse
    {
        $dto = CrearMenuRequest::toDTO($request->validated());

        return response()->json($this->crearMenuCasoUso->ejecutar($dto), 201);
    }

    /**
     * GET /api/menus
     * Lista todos los menus con jerarquia.
     */
    public function index(): JsonResponse
    {
        return response()->json($this->listarMenusCasoUso->ejecutar());
    }

    /**
     * POST /api/menus/{id}/roles
     * Asigna un menu a un rol.
     */
    public function asignarRol(int $id, AsignarMenuRolRequest $request): JsonResponse
    {
        $this->asignarMenuRolCasoUso->ejecutar($id, $request->validated()['rol_id']);

        return response()->json(['message' => 'Menu asignado al rol correctamente.']);
    }

    /**
     * GET /api/menus/por-rol
     * Obtiene los menus asignados al rol del usuario autenticado.
     */
    public function menusPorRol(Request $request): JsonResponse
    {
        $usuarioId = $request->user()->id;

        return response()->json($this->obtenerMenusPorRolCasoUso->ejecutar($usuarioId));
    }
}
