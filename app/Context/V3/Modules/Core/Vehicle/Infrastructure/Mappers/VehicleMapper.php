<?php

namespace App\Context\V3\Modules\Core\Vehicle\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Vehicle\Domain\Mappers\VehicleMapperInterface;
use App\Context\V3\Modules\Core\Vehicle\Domain\Models\Vehicle;
use App\Context\V3\Modules\Core\Vehicle\Infrastructure\Laravel\Eloquent\Models\VehicleModel;

class VehicleMapper implements VehicleMapperInterface
{
    public function toDomain(VehicleModel $record): Vehicle
    {
        return Vehicle::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'plate' => $record->plate,
            'legacy_id' => $record->legacy_id,
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

    public function toDatabaseArray(Vehicle $vehicle): array
    {
        $data = [
            'plate' => $vehicle->plate,
        ];

        if ($vehicle->id !== null) {
            $data['id'] = $vehicle->id;
        }

        if ($vehicle->tenantId !== null) {
            $data['tenant_id'] = $vehicle->tenantId;
        }

        return $data;
    }
}
