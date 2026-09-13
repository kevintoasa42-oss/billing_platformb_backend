<?php

namespace App\Context\V1\Enterprise\Application\Http\Controllers;

use App\Context\V1\Enterprise\Application\UseCases\LoginUseCase;
use App\Context\V1\Enterprise\Application\UseCases\InitialLoginUseCase;
use App\Context\V1\Enterprise\Application\Http\Requests\InitialLoginRequest;
use App\Context\V1\Enterprise\Application\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LoginUseCase $loginCasoUso,
        private InitialLoginUseCase $loginInicialCasoUso,
    ) {}

    /**
     * POST /api/auth/login
     * Valida credenciales y devuelve el user con sus enterprises asignadas.
     * No emite token - el token se emite al seleccionar la enterprise en /api/login.
     */
    public function loginInicial(InitialLoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $resultado = $this->loginInicialCasoUso->ejecutar($data['email'], $data['password']);

            return $this->successResponse($resultado);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    /**
     * POST /api/login
     * Autentica al user y emite un token Sanctum con enterprise_id.
     * Requiere email, password y enterprise_id.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginRequest::toDTO($request->validated());

        try {
            $resultado = $this->loginCasoUso->ejecutar($dto);

            return $this->successResponse($resultado);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    /**
     * GET /api/user
     * Devuelve el user autenticado.
     */
    public function user(\Illuminate\Http\Request $request): JsonResponse
    {
        return $this->successResponse($request->user());
    }
}
