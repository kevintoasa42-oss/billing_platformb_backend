<?php

namespace App\Context\V3\Modules\Core\SriIva\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaPercentage;
use App\Context\V3\Modules\Core\SriIva\Domain\Repository\SriIvaPercentageRepositoryInterface;
use App\Context\V3\Modules\Core\SriIva\Infrastructure\Laravel\Eloquent\Models\SriIvaPercentageModel;

class SriIvaPercentageRepository implements SriIvaPercentageRepositoryInterface
{
    public function findByType(int $sriIvaTypeId): array
    {
        $records = SriIvaPercentageModel::query()
            ->where('sri_iva_type_id', $sriIvaTypeId)
            ->orderBy('start_date', 'desc')
            ->get();

        return $records->map(fn ($record): SriIvaPercentage => $this->toDomain($record))->all();
    }

    public function find(int $id): ?SriIvaPercentage
    {
        $record = SriIvaPercentageModel::query()->find($id);

        return $record !== null ? $this->toDomain($record) : null;
    }

    public function create(array $data): SriIvaPercentage
    {
        $record = SriIvaPercentageModel::query()->create($data);
        $record->refresh();

        return $this->toDomain($record);
    }

    public function update(int $id, array $data): ?SriIvaPercentage
    {
        $record = SriIvaPercentageModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toDomain($record->fresh());
    }

    public function delete(int $id): bool
    {
        $record = SriIvaPercentageModel::query()->find($id);

        if ($record === null) {
            return false;
        }

        $record->update(['is_active' => false]);

        return true;
    }

    private function toDomain(SriIvaPercentageModel $record): SriIvaPercentage
    {
        return new SriIvaPercentage(
            id: (int) $record->id,
            sriIvaTypeId: (int) $record->sri_iva_type_id,
            percentage: (string) $record->percentage,
            startDate: $record->start_date?->toDateString() ?? '',
            endDate: $record->end_date?->toDateString(),
            code: $record->code,
            isActive: (bool) $record->is_active,
        );
    }
}
