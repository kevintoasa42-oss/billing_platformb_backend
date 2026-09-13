<?php

namespace App\Context\V1\Enterprise\Application\Http\Controllers;

use App\Context\V1\Enterprise\Application\UseCases\CreateEnterpriseUseCase;
use App\Context\V1\Enterprise\Application\UseCases\ListEnterprisesUseCase;
use App\Context\V1\Enterprise\Application\Http\Requests\CreateEnterpriseRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EnterpriseController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateEnterpriseUseCase $crearEnterpriseCasoUso,
        private ListEnterprisesUseCase $listarEnterprisesCasoUso,
    ) {}

    /**
     * POST /api/enterprises
     * Crea una enterprise y su base de datos de tenant.
     */
    public function store(CreateEnterpriseRequest $request): JsonResponse
    {
        $dto = CreateEnterpriseRequest::toDTO($request->validated());

        return $this->successResponse($this->crearEnterpriseCasoUso->ejecutar($dto), 201);
    }

    /**
     * GET /api/enterprises
     * Lista todas las enterprises.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse($this->listarEnterprisesCasoUso->ejecutar());
    }
}
