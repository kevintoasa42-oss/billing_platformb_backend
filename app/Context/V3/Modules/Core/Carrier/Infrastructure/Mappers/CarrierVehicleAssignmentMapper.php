<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers;

use App\Context\V3\Shared\Infrastructure\Postgres\DaterangeNormalizer;
use Illuminate\Support\Str;

/**
 * Maps raw vehicle assignment arrays (from DTOs) into database-ready rows
 * for core.carrier_vehicle_assignments.
 */
class CarrierVehicleAssignmentMapper
{
    /**
     * @param  array<string, mixed>  $va
     * @return array<string, mixed>
     */
    public function toDatabaseArray(string $tenantId, array $va): array
    {
        return [
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'vehicle_id' => $va['vehicle_id'],
            'validity' => DaterangeNormalizer::normalize($va['validity'] ?? null),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $vehicleAssignments
     * @return array<int, array<string, mixed>>
     */
    public function toDatabaseArrayList(string $tenantId, array $vehicleAssignments): array
    {
        return array_map(
            fn (array $va) => $this->toDatabaseArray($tenantId, $va),
            $vehicleAssignments
        );
    }
}
