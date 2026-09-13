<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;

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
