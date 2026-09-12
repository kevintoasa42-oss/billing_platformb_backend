<?php

namespace App\Contexto\Enterprise\Aplicacion\Http\Controllers;

use App\Contexto\Enterprise\Aplicacion\CasosDeUso\AsignarEmpresaAUsuarioCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\AsignarRolAUsuarioCasoUso;
use App\Contexto\Enterprise\Aplicacion\CasosDeUso\CrearUsuarioCasoUso;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\AsignarEmpresaRequest;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\AsignarRolRequest;
use App\Contexto\Enterprise\Aplicacion\Http\Requests\CrearUsuarioRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    public function __construct(
        private CrearUsuarioCasoUso $crearUsuarioCasoUso,
        private AsignarRolAUsuarioCasoUso $asignarRolCasoUso,
        private AsignarEmpresaAUsuarioCasoUso $asignarEmpresaCasoUso,
    ) {}

    /**
     * POST /api/usuarios
     * Crea un usuario.
     */
    public function store(CrearUsuarioRequest $request): JsonResponse
    {
        $dto = CrearUsuarioRequest::toDTO($request->validated());

        return response()->json($this->crearUsuarioCasoUso->ejecutar($dto), 201);
    }

    /**
     * POST /api/usuarios/{id}/roles
     * Asigna un rol a un usuario.
     */
    public function asignarRol(int $id, AsignarRolRequest $request): JsonResponse
    {
        $this->asignarRolCasoUso->ejecutar($id, $request->validated()['rol_id']);

        return response()->json(['message' => 'Rol asignado correctamente.']);
    }

    /**
     * POST /api/usuarios/{id}/empresas
     * Asigna una empresa a un usuario.
     */
    public function asignarEmpresa(int $id, AsignarEmpresaRequest $request): JsonResponse
    {
        $this->asignarEmpresaCasoUso->ejecutar($id, $request->validated()['enterprise_id']);

        return response()->json(['message' => 'Empresa asignada correctamente.']);
    }
}
