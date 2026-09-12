<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Controllers;

use App\Contexto\Enterprise\Aplicacion\CasosDeUso\CrearEmpresaCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\ListarEmpresasCasoUso;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\CrearEmpresaRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class EmpresaController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CrearEmpresaCasoUso $crearEmpresaCasoUso,
        private ListarEmpresasCasoUso $listarEmpresasCasoUso,
    ) {}

    /**
     * POST /api/empresas
     * Crea una empresa y su base de datos de tenant.
     */
    public function store(CrearEmpresaRequest $request): JsonResponse
    {
        $dto = CrearEmpresaRequest::toDTO($request->validated());

        return $this->successResponse($this->crearEmpresaCasoUso->ejecutar($dto), 201);
    }

    /**
     * GET /api/empresas
     * Lista todas las empresas.
     */
    public function index(): JsonResponse
    {
        return $this->successResponse($this->listarEmpresasCasoUso->ejecutar());
    }
}
