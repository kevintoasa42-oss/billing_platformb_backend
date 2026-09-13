<?php

namespace App\Context\V1\Carrier\Application\UseCases;

use App\Context\V1\Carrier\Application\DTOs\CarrierDTO;
use App\Context\V1\Carrier\Domain\Mappers\CarrierMapper;
use App\Context\V1\Carrier\Domain\Repositories\CarrierRepositoryInterface;

class UpdateCarrierUseCase
{
    public function __construct(
        private CarrierRepositoryInterface $repository,
    ) {}

    public function execute(CarrierDTO $dto): CarrierDTO
    {
        $carrier = CarrierMapper::fromDto($dto);

        return CarrierDTO::fromArray($this->repository->update($carrier));
    }
}
