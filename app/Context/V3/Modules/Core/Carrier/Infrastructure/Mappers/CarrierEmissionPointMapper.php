<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEmissionPointModel as CarrierEmissionPointEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEmissionPoint;

class CarrierEmissionPointMapper
{
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

    /**
     * Build the enriched response array with the nested establishment
     * and its carrier company name.
     *
     * @return array<string, mixed>
     */
    public function toResponseArray(CarrierEmissionPointEloquentModel $record): array
    {
        $establishment = null;
        if ($record->establishment !== null) {
            $est = $record->establishment;
            $establishment = [
                'id' => $est->id,
                'name' => $est->name,
                'sri_code' => $est->sri_code,
                'carrier_company_id' => $est->carrier_company_id,
                'carrier_company_name' => $est->carrierCompany?->legal_name,
            ];
        }

        return [
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'establishment_id' => $record->establishment_id,
            'establishment' => $establishment,
            'sri_code' => $record->sri_code,
            'name' => $record->name,
            'next_sequential' => $record->next_sequential,
            'is_active' => $record->is_active,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toResponseArrayList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toResponseArray($record);
        }

        return $list;
    }
}
