<?php

namespace App\Context\V3\Modules\Core\Vehicle\Domain\Mappers;

use App\Context\V3\Modules\Core\Vehicle\Domain\Models\Vehicle;
use App\Context\V3\Modules\Core\Vehicle\Infrastructure\Laravel\Eloquent\Models\VehicleModel;

interface VehicleMapperInterface
{
    public function toDomain(VehicleModel $record): Vehicle;

    /**
     * @param  iterable<VehicleModel>  $records
     * @return array<int, Vehicle>
     */
    public function toDomainList(iterable $records): array;

    /**
     * @return array<string, mixed>
     */
    public function toDatabaseArray(Vehicle $vehicle): array;
}
