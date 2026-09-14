<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers\EmissionPointMapper;

class EmissionPointRepository implements EmissionPointRepositoryInterface
{
    public function __construct(
        private readonly EmissionPointMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = EmissionPointModel::query()
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?EmissionPoint
    {
        $record = EmissionPointModel::query()->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function byEstablishment(string $establishmentId): array
    {
        $records = EmissionPointModel::query()
            ->where('establishment_id', $establishmentId)
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function create(EmissionPoint $emissionPoint): EmissionPoint
    {
        $record = EmissionPointModel::query()->create(
            $this->mapper->toDatabaseArray($emissionPoint)
        );

        return $this->mapper->toDomain($record);
    }

    public function update(string $id, EmissionPoint $emissionPoint): ?EmissionPoint
    {
        $record = EmissionPointModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($this->mapper->toDatabaseArray($emissionPoint));

        return $this->mapper->toDomain($record->fresh());
    }
}
