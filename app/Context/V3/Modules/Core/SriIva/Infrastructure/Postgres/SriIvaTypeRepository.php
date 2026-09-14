<?php

namespace App\Context\V3\Modules\Core\SriIva\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaPercentage;
use App\Context\V3\Modules\Core\SriIva\Domain\Models\SriIvaType;
use App\Context\V3\Modules\Core\SriIva\Domain\Repository\SriIvaTypeRepositoryInterface;
use App\Context\V3\Modules\Core\SriIva\Infrastructure\Laravel\Eloquent\Models\SriIvaTypeModel;

class SriIvaTypeRepository implements SriIvaTypeRepositoryInterface
{
    public function all(): array
    {
        $records = SriIvaTypeModel::query()->with('percentages')->get();

        return $records->map(fn (SriIvaTypeModel $record): SriIvaType => $this->toDomain($record))->all();
    }

    public function find(int $id): ?SriIvaType
    {
        $record = SriIvaTypeModel::query()->with('percentages')->find($id);

        return $record !== null ? $this->toDomain($record) : null;
    }

    public function create(array $data): SriIvaType
    {
        $record = SriIvaTypeModel::query()->create($data);
        $record->refresh();

        return $this->toDomain($record->fresh('percentages') ?? $record);
    }

    public function update(int $id, array $data): ?SriIvaType
    {
        $record = SriIvaTypeModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($data);

        return $this->toDomain($record->fresh('percentages'));
    }

    public function delete(int $id): bool
    {
        $record = SriIvaTypeModel::query()->find($id);

        if ($record === null) {
            return false;
        }

        $record->update(['is_active' => false]);

        return true;
    }

    private function toDomain(SriIvaTypeModel $record): SriIvaType
    {
        return new SriIvaType(
            id: (int) $record->id,
            name: $record->name,
            percentage: (string) $record->percentage,
            sriCode: $record->sri_code,
            isActive: (bool) $record->is_active,
            percentages: $record->relationLoaded('percentages')
                ? $record->percentages->map(
                    static fn ($p): SriIvaPercentage => SriIvaPercentage::fromArray([
                        'id' => $p->id,
                        'sri_iva_type_id' => $p->sri_iva_type_id,
                        'percentage' => (string) $p->percentage,
                        'start_date' => $p->start_date?->toDateString(),
                        'end_date' => $p->end_date?->toDateString(),
                        'code' => $p->code,
                        'is_active' => $p->is_active,
                    ])
                )->all()
                : [],
        );
    }
}
