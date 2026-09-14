<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEstablishmentModel as CarrierEstablishmentEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;

class CarrierEstablishmentMapper
{
    public function toDomain(CarrierEstablishmentEloquentModel $record): CarrierEstablishment
    {
        $activityIds = null;
        if ($record->relationLoaded('carrierCompany') && $record->carrierCompany !== null && $record->carrierCompany->relationLoaded('activities')) {
            $activityIds = $record->carrierCompany->activities->pluck('activity_id')->all();
        }

        return CarrierEstablishment::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'carrier_company_id' => $record->carrier_company_id,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'address' => $record->address,
            'phone' => $record->phone,
            'email' => $record->email,
            'city_id' => $record->city_id,
            'is_active' => $record->is_active,
            'activity_ids' => $activityIds,
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

    public function toDatabaseArray(CarrierEstablishment $establishment): array
    {
        $data = [
            'carrier_company_id' => $establishment->carrierCompanyId,
            'sri_code' => $establishment->sriCode,
            'name' => $establishment->name,
            'address' => $establishment->address,
            'phone' => $establishment->phone,
            'email' => $establishment->email,
            'city_id' => $establishment->cityId,
            'is_active' => $establishment->isActive ?? true,
        ];

        if ($establishment->id !== null) {
            $data['id'] = $establishment->id;
        }

        if ($establishment->tenantId !== null) {
            $data['tenant_id'] = $establishment->tenantId;
        }

        return $data;
    }
}
