<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEmissionPointModel as CarrierEmissionPointEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;

class CarrierEmissionPointMapper
{
    public function toDomain(CarrierEmissionPointEloquentModel $record): CarrierEmissionPoint
    {
        return CarrierEmissionPoint::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'establishment_id' => $record->establishment_id,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'next_sequential' => $record->next_sequential,
            'is_active' => $record->is_active,
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

    public function toDatabaseArray(CarrierEmissionPoint $emissionPoint): array
    {
        $data = [
            'establishment_id' => $emissionPoint->establishmentId,
            'sri_code' => $emissionPoint->sriCode,
            'name' => $emissionPoint->name,
            'is_active' => $emissionPoint->isActive ?? true,
        ];

        if ($emissionPoint->id !== null) {
            $data['id'] = $emissionPoint->id;
        }

        if ($emissionPoint->tenantId !== null) {
            $data['tenant_id'] = $emissionPoint->tenantId;
        }

        return $data;
    }
}
