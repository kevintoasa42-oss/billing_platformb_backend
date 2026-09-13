<?php

namespace App\Context\Enterprise\Application\Http\Controllers;

use App\Context\Enterprise\Application\UseCases\AssignEnterpriseToUserUseCase;
use App\Context\Enterprise\Application\UseCases\AssignRoleToUserUseCase;
use App\Context\Enterprise\Application\UseCases\CreateUserUseCase;
use App\Context\Enterprise\Application\Http\Requests\AssignEnterpriseRequest;
use App\Context\Enterprise\Application\Http\Requests\AssignRoleRequest;
use App\Context\Enterprise\Application\Http\Requests\CreateUserRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateUserUseCase $crearUserCasoUso,
        private AssignRoleToUserUseCase $asignarRolCasoUso,
        private AssignEnterpriseToUserUseCase $asignarEnterpriseCasoUso,
    ) {}

    /**
     * POST /api/users
     * Crea un user.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        $dto = CreateUserRequest::toDTO($request->validated());

        return $this->successResponse($this->crearUserCasoUso->ejecutar($dto), 201);
    }

    /**
     * POST /api/users/{id}/roles
     * Asigna un rol a un user.
     */
    public function asignarRol(int $id, AssignRoleRequest $request): JsonResponse
    {
        $this->asignarRolCasoUso->ejecutar($id, $request->validated()['role_id']);

        return $this->successResponse('Role asignado correctamente.');
    }

    /**
     * POST /api/users/{id}/enterprises
     * Asigna una enterprise a un user.
     */
    public function asignarEnterprise(int $id, AssignEnterpriseRequest $request): JsonResponse
    {
        $this->asignarEnterpriseCasoUso->ejecutar($id, $request->validated()['enterprise_id']);

        return $this->successResponse('Enterprise asignada correctamente.');
    }
}
