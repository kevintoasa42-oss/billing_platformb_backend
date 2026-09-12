<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Controllers;

use App\Contexto\Enterprise\Aplicacion\CasosDeUso\LoginCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\LoginInicialCasoUso;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\LoginInicialRequest;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private LoginCasoUso $loginCasoUso,
        private LoginInicialCasoUso $loginInicialCasoUso,
    ) {}

    /**
     * POST /api/auth/login
     * Valida credenciales y devuelve el usuario con sus empresas asignadas.
     * No emite token - el token se emite al seleccionar la empresa en /api/login.
     */
    public function loginInicial(LoginInicialRequest $request): JsonResponse
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
     * Autentica al usuario y emite un token Sanctum con enterprise_id.
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
     * Devuelve el usuario autenticado.
     */
    public function user(\Illuminate\Http\Request $request): JsonResponse
    {
        return $this->successResponse($request->user());
    }
}
