<?php

namespace App\Context\V1\Modules\Product\Application\UseCases;

use App\Context\V1\Modules\Product\Domain\Repositories\ProductRepositoryInterface;

class ChangeProductStatusUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $id, bool $status): bool
    {
        return $this->repository->changeStatus($id, $status);
    }
}
