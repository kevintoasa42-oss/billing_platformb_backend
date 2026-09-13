<?php

namespace App\Contexto\Product\Aplicacion\CasosDeUso;

use App\Contexto\Product\Aplicacion\DTOs\ProductoDTO;
use App\Contexto\Product\Dominio\Repositorios\ProductoRepositoryInterface;

class ListarProductosCasoUso
{
    public function __construct(
        private ProductoRepositoryInterface $repository,
    ) {}

    /**
     * Lista paginada de productos.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array
     */
    public function ejecutar(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        return $this->repository->listarPaginado($page, $perPage, $search);
    }
}
