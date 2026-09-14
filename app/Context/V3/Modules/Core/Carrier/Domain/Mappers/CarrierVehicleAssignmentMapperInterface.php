<?php

namespace App\Context\V3\Modules\Core\Carrier\Domain\Mappers;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierVehicleAssignmentModel as CarrierVehicleAssignmentEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierVehicleAssignment;

interface CarrierVehicleAssignmentMapperInterface
{
    public function toDomain(CarrierVehicleAssignmentEloquentModel $record): CarrierVehicleAssignment;

    /**
     * @param  iterable<CarrierVehicleAssignmentEloquentModel>  $records
     * @return array<int, CarrierVehicleAssignment>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(CarrierVehicleAssignment $assignment): array;
}
