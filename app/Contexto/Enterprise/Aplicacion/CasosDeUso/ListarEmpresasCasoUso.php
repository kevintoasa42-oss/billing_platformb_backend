<?php

namespace App\Contexto\Enterprise\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Aplicacion\DTOs\EmpresaDTO;
use App\Contexto\Enterprise\Dominio\Repositorios\EmpresaRepositoryInterface;

class ListarEmpresasCasoUso
{
    public function __construct(
        private EmpresaRepositoryInterface $empresaRepository,
    ) {}

    /**
     * Lista todas las empresas.
     *
     * @return EmpresaDTO[]
     */
    public function ejecutar(): array
    {
        $empresas = $this->empresaRepository->listar();

        return array_map(
            fn ($e) => EmpresaDTO::fromArray([
                'id' => $e->id,
                'nombre' => $e->nombre,
                'ruc' => $e->ruc,
                'tradename' => $e->tradename,
                'matrixname' => $e->matrixname,
                'telefono' => $e->telefono,
                'correo_corporativo' => $e->correo_corporativo,
            ]),
            $empresas
        );
    }
}
