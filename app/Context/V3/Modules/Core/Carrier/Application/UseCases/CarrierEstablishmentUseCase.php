<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierEstablishmentCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEstablishmentRepositoryInterface;

class CarrierEstablishmentUseCase
{
    public function __construct(
        private readonly CarrierEstablishmentRepositoryInterface $repository,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->repository->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $id): ?array
    {
        return $this->repository->find($id);
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CarrierEstablishmentCreateDTO $dto): array
    {
        $establishment = CarrierEstablishment::fromArray($dto->toArray());

        return $this->repository->create($establishment);
    }
}
