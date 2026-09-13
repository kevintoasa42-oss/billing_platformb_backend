<?php

namespace App\Context\Product\Application\UseCases;

use App\Context\Product\Domain\Repositories\ProductRepositoryInterface;

class ChangeProductStatusUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function ejecutar(int $id, bool $status): bool
    {
        return $this->repository->cambiarEstado($id, $status);
    }
}
