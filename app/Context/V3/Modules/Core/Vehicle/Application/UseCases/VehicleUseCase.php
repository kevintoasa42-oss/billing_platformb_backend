<?php

namespace App\Context\V3\Modules\Core\Vehicle\Application\UseCases;

use App\Context\V3\Modules\Core\Vehicle\Application\DTOs\VehicleCreateDTO;
use App\Context\V3\Modules\Core\Vehicle\Application\DTOs\VehicleUpdateDTO;
use App\Context\V3\Modules\Core\Vehicle\Domain\Models\Vehicle;
use App\Context\V3\Modules\Core\Vehicle\Domain\Repository\VehicleRepositoryInterface;

class VehicleUseCase
{
    public function __construct(
        private readonly VehicleRepositoryInterface $repository,
    ) {}

    public function all(): array
    {
        $vehicles = $this->repository->all();

        return array_map(fn (Vehicle $v) => $v->toArray(), $vehicles);
    }

    public function find(string $id): ?array
    {
        $vehicle = $this->repository->find($id);

        return $vehicle?->toArray();
    }

    public function create(VehicleCreateDTO $dto): array
    {
        $vehicle = Vehicle::fromArray($dto->toArray());

        $created = $this->repository->create($vehicle);

        return $created->toArray();
    }

    public function update(string $id, VehicleUpdateDTO $dto): ?array
    {
        $existing = $this->repository->find($id);

        if ($existing === null) {
            return null;
        }

        $merged = array_merge($existing->toArray(), $dto->toArray());

        $vehicle = Vehicle::fromArray($merged);

        $updated = $this->repository->update($id, $vehicle);

        return $updated?->toArray();
    }
}
