<?php

namespace App\Context\Product\Domain\Repositories;

use App\Context\Product\Domain\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Lista paginada de products.
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $search
     * @return array{data: Product[], total: int, page: int, perPage: int, lastPage: int}
     */
    public function listarPaginado(int $page = 1, int $perPage = 15, ?string $search = null): array;

    public function obtenerPorId(int $id): ?Product;

    public function crear(Product $product): Product;

    public function actualizar(Product $product): Product;

    public function cambiarEstado(int $id, bool $estado): bool;

    public function asignarImpuestos(int $productId, array $impuestoIds): void;
}
