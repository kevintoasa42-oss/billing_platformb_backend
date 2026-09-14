<?php

namespace App\Context\V3\Modules\Core\Carrier\Application\UseCases;

use App\Context\V3\Modules\Core\Carrier\Application\DTOs\CarrierEmissionPointCreateDTO;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEmissionPointRepositoryInterface;

class CarrierEmissionPointUseCase
{
    public function __construct(
        private readonly CarrierEmissionPointRepositoryInterface $repository,
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
     * @return array<int, array<string, mixed>>
     */
    public function byEstablishment(string $establishmentId): array
    {
        return $this->repository->byEstablishment($establishmentId);
    }

    /**
     * @return array<string, mixed>
     */
    public function create(CarrierEmissionPointCreateDTO $dto): array
    {
        $emissionPoint = CarrierEmissionPoint::fromArray($dto->toArray());

        return $this->repository->create($emissionPoint);
    }
}
