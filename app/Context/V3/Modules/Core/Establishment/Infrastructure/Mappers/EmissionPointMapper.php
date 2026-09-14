<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Establishment\Domain\Mappers\EmissionPointMapperInterface;
use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;

class EmissionPointMapper implements EmissionPointMapperInterface
{
    public function toDomain(EmissionPointModel $record): EmissionPoint
    {
        return EmissionPoint::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'establishment_id' => $record->establishment_id,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'legacy_id' => $record->legacy_id,
            'is_active' => $record->is_active,
            'is_default' => $record->is_default,
            'has_tax_validity' => $record->has_tax_validity,
        ]);
    }

    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    public function toDatabaseArray(EmissionPoint $emissionPoint): array
    {
        $data = $emissionPoint->toArray();

        if (($data['id'] ?? null) === null) {
            unset($data['id']);
        }

        if (($data['tenant_id'] ?? null) === null) {
            unset($data['tenant_id']);
        }

        if (($data['legacy_id'] ?? null) === null) {
            unset($data['legacy_id']);
        }

        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;
        $data['has_tax_validity'] = $data['has_tax_validity'] ?? true;

        return $data;
    }
}
