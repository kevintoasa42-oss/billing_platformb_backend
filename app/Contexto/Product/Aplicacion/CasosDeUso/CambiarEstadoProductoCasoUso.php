<?php

namespace App\Contexto\Product\Aplicacion\CasosDeUso;

use App\Contexto\Product\Dominio\Repositorios\ProductoRepositoryInterface;

class CambiarEstadoProductoCasoUso
{
    public function __construct(
        private ProductoRepositoryInterface $repository,
    ) {}

    public function ejecutar(int $id, bool $estado): bool
    {
        return $this->repository->cambiarEstado($id, $estado);
    }
}
