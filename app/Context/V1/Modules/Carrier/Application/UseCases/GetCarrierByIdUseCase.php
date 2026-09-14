<?php

namespace App\Context\V1\Modules\Carrier\Application\UseCases;

use App\Context\V1\Modules\Carrier\Application\DTOs\CarrierDTO;
use App\Context\V1\Modules\Carrier\Domain\Repositories\CarrierRepositoryInterface;

class GetCarrierByIdUseCase
{
    public function __construct(
        private CarrierRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?CarrierDTO
    {
        $data = $this->repository->getById($id);

        if (!$data) {
            return null;
        }

        return CarrierDTO::fromArray($data);
    }
}
