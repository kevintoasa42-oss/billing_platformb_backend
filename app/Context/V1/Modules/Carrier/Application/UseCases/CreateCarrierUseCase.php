<?php

namespace App\Context\V1\Modules\Carrier\Application\UseCases;

use App\Context\V1\Modules\Carrier\Application\DTOs\CarrierDTO;
use App\Context\V1\Modules\Carrier\Domain\Mappers\CarrierMapper;
use App\Context\V1\Modules\Carrier\Domain\Repositories\CarrierRepositoryInterface;

class CreateCarrierUseCase
{
    public function __construct(
        private CarrierRepositoryInterface $repository,
    ) {}

    public function execute(CarrierDTO $dto): CarrierDTO
    {
        $carrier = CarrierMapper::fromDto($dto);

        return CarrierDTO::fromArray($this->repository->create($carrier));
    }
}
