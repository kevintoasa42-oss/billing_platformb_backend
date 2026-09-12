<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Controllers;

use App\Contexto\Enterprise\Aplicacion\CasosDeUso\LoginCasoUso;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private LoginCasoUso $loginCasoUso,
    ) {}

    /**
     * POST /api/login
     * Autentica al usuario y emite un token Sanctum con enterprise_id.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = LoginRequest::toDTO($request->validated());

        try {
            $resultado = $this->loginCasoUso->ejecutar($dto);

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 401);
        }
    }

    /**
     * GET /api/user
     * Devuelve el usuario autenticado.
     */
    public function user(\Illuminate\Http\Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
