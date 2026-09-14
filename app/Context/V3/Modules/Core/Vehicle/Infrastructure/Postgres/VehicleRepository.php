<?php

namespace App\Context\V3\Modules\Core\Vehicle\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Vehicle\Domain\Models\Vehicle;
use App\Context\V3\Modules\Core\Vehicle\Domain\Repository\VehicleRepositoryInterface;
use App\Context\V3\Modules\Core\Vehicle\Infrastructure\Laravel\Eloquent\Models\VehicleModel;
use App\Context\V3\Modules\Core\Vehicle\Infrastructure\Mappers\VehicleMapper;

class VehicleRepository implements VehicleRepositoryInterface
{
    public function __construct(
        private readonly VehicleMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = VehicleModel::query()
            ->orderBy('plate')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?Vehicle
    {
        $record = VehicleModel::query()->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(Vehicle $vehicle): Vehicle
    {
        $record = VehicleModel::query()->create(
            $this->mapper->toDatabaseArray($vehicle)
        );

        return $this->mapper->toDomain($record);
    }

    public function update(string $id, Vehicle $vehicle): ?Vehicle
    {
        $record = VehicleModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($this->mapper->toDatabaseArray($vehicle));

        return $this->mapper->toDomain($record->fresh());
    }
}
