<?php

namespace App\Context\Product\Application\UseCases;

use App\Context\Product\Application\DTOs\ProductDTO;
use App\Context\Product\Domain\Repositories\ProductRepositoryInterface;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    /**
     * Lista paginada de products.
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
