<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierAffiliationModel as CarrierAffiliationEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierAffiliation;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierAffiliationRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers\CarrierAffiliationMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarrierAffiliationRepository implements CarrierAffiliationRepositoryInterface
{
    public function __construct(
        private readonly CarrierAffiliationMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = CarrierAffiliationEloquentModel::with('vehicleAssignments')->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?CarrierAffiliation
    {
        $record = CarrierAffiliationEloquentModel::with('vehicleAssignments')->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(CarrierAffiliation $affiliation): CarrierAffiliation
    {
        return DB::connection('master_v3')->transaction(function () use ($affiliation): CarrierAffiliation {
            $record = CarrierAffiliationEloquentModel::query()->create(
                $this->mapper->toDatabaseArray($affiliation)
            );

            if ($affiliation->vehicleAssignments !== null) {
                $record->vehicleAssignments()->createMany(
                    $this->mapVehicleAssignments($record->tenant_id, $affiliation->vehicleAssignments)
                );
            }

            return $this->mapper->toDomain($record->load('vehicleAssignments'));
        });
    }

    public function update(string $id, CarrierAffiliation $affiliation): ?CarrierAffiliation
    {
        $record = CarrierAffiliationEloquentModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        return DB::connection('master_v3')->transaction(function () use ($record, $affiliation): CarrierAffiliation {
            $record->update($this->mapper->toDatabaseArray($affiliation));

            if ($affiliation->vehicleAssignments !== null) {
                $record->vehicleAssignments()->delete();
                $record->vehicleAssignments()->createMany(
                    $this->mapVehicleAssignments($record->tenant_id, $affiliation->vehicleAssignments)
                );
            }

            return $this->mapper->toDomain($record->load('vehicleAssignments'));
        });
    }

    public function findByThirdPartyId(string $thirdPartyId): ?CarrierAffiliation
    {
        $record = CarrierAffiliationEloquentModel::query()
            ->where('third_party_id', $thirdPartyId)
            ->with('vehicleAssignments')
            ->first();

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function platesByThirdPartyIds(array $thirdPartyIds): array
    {
        if (empty($thirdPartyIds)) {
            return [];
        }

        $records = CarrierAffiliationEloquentModel::query()
            ->whereIn('third_party_id', $thirdPartyIds)
            ->with(['vehicleAssignments.vehicle'])
            ->get();

        $map = [];
        foreach ($records as $affiliation) {
            $tpId = $affiliation->third_party_id;
            if (! isset($map[$tpId])) {
                $map[$tpId] = [];
            }
            foreach ($affiliation->vehicleAssignments as $va) {
                if ($va->vehicle !== null && ! in_array($va->vehicle->plate, $map[$tpId], true)) {
                    $map[$tpId][] = $va->vehicle->plate;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $vehicleAssignments
     * @return array<int, array<string, mixed>>
     */
    private function mapVehicleAssignments(string $tenantId, array $vehicleAssignments): array
    {
        return array_map(fn (array $va) => [
            'id' => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'vehicle_id' => $va['vehicle_id'],
            'validity' => $va['validity'] ?? null,
        ], $vehicleAssignments);
    }
}
