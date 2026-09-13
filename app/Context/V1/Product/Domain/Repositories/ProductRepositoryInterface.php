<?php

namespace App\Context\V1\Product\Domain\Repositories;

use App\Context\V1\Product\Domain\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Paginated list of products.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array{data: Product[], total: int, page: int, perPage: int, lastPage: int}
     */
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array;

    public function getById(int $id): ?Product;

    public function create(Product $product): Product;

    public function update(Product $product): Product;

    public function changeStatus(int $id, bool $status): bool;

    public function assignTaxes(int $productId, array $taxIds): void;
}
