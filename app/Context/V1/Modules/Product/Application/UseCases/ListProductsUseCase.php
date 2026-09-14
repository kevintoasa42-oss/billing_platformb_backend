<?php

namespace App\Context\V1\Modules\Product\Application\UseCases;

use App\Context\V1\Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    /**
     * Paginated list of products.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array
     */
    public function execute(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        return $this->repository->listPaginated($page, $perPage, $search);
    }
}
