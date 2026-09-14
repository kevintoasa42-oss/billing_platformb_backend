<?php

namespace App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\EconomicActivity\Domain\Models\EconomicActivity;
use App\Context\V3\Modules\Core\EconomicActivity\Domain\Repository\EconomicActivityRepositoryInterface;
use App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Laravel\Eloquent\Models\EconomicActivityModel;
use App\Context\V3\Modules\Core\EconomicActivity\Infrastructure\Mappers\EconomicActivityMapper;

class EconomicActivityRepository implements EconomicActivityRepositoryInterface
{
    public function __construct(
        private readonly EconomicActivityMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = EconomicActivityModel::query()
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?EconomicActivity
    {
        $record = EconomicActivityModel::query()->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(EconomicActivity $activity): EconomicActivity
    {
        $record = EconomicActivityModel::query()->create(
            $this->mapper->toDatabaseArray($activity)
        );

        return $this->mapper->toDomain($record);
    }

    public function update(string $id, EconomicActivity $activity): ?EconomicActivity
    {
        $record = EconomicActivityModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($this->mapper->toDatabaseArray($activity));

        return $this->mapper->toDomain($record->fresh());
    }
}
