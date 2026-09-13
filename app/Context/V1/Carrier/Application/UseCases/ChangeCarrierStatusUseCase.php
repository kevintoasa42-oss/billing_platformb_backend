<?php

namespace App\Context\V1\Carrier\Application\UseCases;

use App\Context\V1\Carrier\Domain\Repositories\CarrierRepositoryInterface;

class ChangeCarrierStatusUseCase
{
    public function __construct(
        private CarrierRepositoryInterface $repository,
    ) {}

    public function execute(int $id, bool $status): bool
    {
        return $this->repository->changeStatus($id, $status);
    }
}
