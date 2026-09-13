<?php

namespace App\Context\Menu\Application\Http\Controllers;

use App\Context\Menu\Application\UseCases\AssignMenuToRoleUseCase;
use App\Context\Menu\Application\UseCases\CreateMenuUseCase;
use App\Context\Menu\Application\UseCases\ListMenusUseCase;
use App\Context\Menu\Application\UseCases\GetMenusByRoleUseCase;
use App\Context\Menu\Application\Http\Requests\AssignMenuRoleRequest;
use App\Context\Menu\Application\Http\Requests\CreateMenuRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateMenuUseCase $crearMenuCasoUso,
        private ListMenusUseCase $listarMenusCasoUso,
        private AssignMenuToRoleUseCase $asignarMenuRolCasoUso,
        private GetMenusByRoleUseCase $obtenerMenusPorRolCasoUso,
    ) {}

    /**
     * POST /api/menus
     * Crea un menu.
     */
    public function store(CreateMenuRequest $request): JsonResponse
    {
        $dto = CreateMenuRequest::toDTO($request->validated());

        return $this->successResponse($this->crearMenuCasoUso->ejecutar($dto), 201);
    }

    /**
     * GET /api/menus
     * Lista todos los menus con jerarquia.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse($this->listarMenusCasoUso->ejecutar());
    }

    /**
     * POST /api/menus/{id}/roles
     * Asigna un menu a un rol.
     */
    public function asignarRol(int $id, AssignMenuRoleRequest $request): JsonResponse
    {
        $this->asignarMenuRolCasoUso->ejecutar($id, $request->validated()['role_id']);

        return $this->successResponse('Menu asignado al rol correctamente.');
    }

    /**
     * GET /api/menus/by-role
     * Obtiene los menus asignados al rol del user autenticado.
     */
    public function menusByRole(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        return $this->successResponse($this->obtenerMenusPorRolCasoUso->ejecutar($userId));
    }
}
