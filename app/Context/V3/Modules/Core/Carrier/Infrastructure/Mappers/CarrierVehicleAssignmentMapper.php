<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierVehicleAssignmentModel as CarrierVehicleAssignmentEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Mappers\CarrierVehicleAssignmentMapperInterface;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierVehicleAssignment;

class CarrierVehicleAssignmentMapper implements CarrierVehicleAssignmentMapperInterface
{
    public function toDomain(CarrierVehicleAssignmentEloquentModel $record): CarrierVehicleAssignment
    {
        return CarrierVehicleAssignment::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'affiliation_id' => $record->affiliation_id,
            'vehicle_id' => $record->vehicle_id,
            'validity' => $record->validity,
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

    public function toDatabaseArray(CarrierVehicleAssignment $assignment): array
    {
        return $assignment->toArray();
    }
}
