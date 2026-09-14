<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierAffiliationModel as CarrierAffiliationEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Mappers\CarrierAffiliationMapperInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierAffiliation;

class CarrierAffiliationMapper implements CarrierAffiliationMapperInterface
{
    public function toDomain(CarrierAffiliationEloquentModel $record): CarrierAffiliation
    {
        $vehicleAssignments = null;

        if ($record->relationLoaded('vehicleAssignments')) {
            $vehicleAssignments = $record->vehicleAssignments->map(fn ($va) => [
                'vehicle_id' => $va->vehicle_id,
                'validity' => $va->validity,
            ])->all();
        }

        return CarrierAffiliation::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'third_party_id' => $record->third_party_id,
            'validity' => $record->validity,
            'vehicle_assignments' => $vehicleAssignments,
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

    public function toDatabaseArray(CarrierAffiliation $affiliation): array
    {
        $data = [
            'third_party_id' => $affiliation->thirdPartyId,
            'validity' => $affiliation->validity,
        ];

        if ($affiliation->id !== null) {
            $data['id'] = $affiliation->id;
        }

        if ($affiliation->tenantId !== null) {
            $data['tenant_id'] = $affiliation->tenantId;
        }

        return $data;
    }
}
