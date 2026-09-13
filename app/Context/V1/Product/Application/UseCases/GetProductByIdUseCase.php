<?php

namespace App\Context\V1\Product\Application\UseCases;

use App\Context\V1\Product\Application\DTOs\ProductDTO;
use App\Context\V1\Product\Domain\Repositories\ProductRepositoryInterface;

class GetProductByIdUseCase
{
    public function __construct(
        private ProductRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?ProductDTO
    {
        $data = $this->repository->getById($id);

        if (!$data) {
            return null;
        }

        return ProductDTO::fromArray($data);
    }
}
