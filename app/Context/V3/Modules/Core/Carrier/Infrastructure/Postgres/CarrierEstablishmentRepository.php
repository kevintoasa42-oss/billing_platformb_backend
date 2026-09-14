<?php

namespace App\Context\V3\Modules\Core\Carrier\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Carrier\Infrastructure\Laravel\Eloquent\Models\CarrierEstablishmentModel as CarrierEstablishmentEloquentModel;
use App\Context\V3\Modules\Core\Carrier\Domain\Models\CarrierEstablishment;
use App\Context\V3\Modules\Core\Carrier\Domain\Repository\CarrierEstablishmentRepositoryInterface;
use App\Context\V3\Modules\Core\Carrier\Infrastructure\Mappers\CarrierEstablishmentMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CarrierEstablishmentRepository implements CarrierEstablishmentRepositoryInterface
{
    public function __construct(
        private readonly CarrierEstablishmentMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = CarrierEstablishmentEloquentModel::query()
            ->with(['carrierCompany.thirdParty', 'carrierCompany.activities.economicActivity'])
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?CarrierEstablishment
    {
        $record = CarrierEstablishmentEloquentModel::query()
            ->with(['carrierCompany.thirdParty', 'carrierCompany.activities.economicActivity'])
            ->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(CarrierEstablishment $establishment): CarrierEstablishment
    {
        return DB::connection('master_v3')->transaction(function () use ($establishment): CarrierEstablishment {
            $record = CarrierEstablishmentEloquentModel::query()->create(
                $this->mapper->toDatabaseArray($establishment)
            );

            $this->syncActivities($record, $establishment->activityIds);

            return $this->mapper->toDomain($record->load(['carrierCompany.thirdParty', 'carrierCompany.activities.economicActivity']));
        });
    }

    /**
     * Sync activities into carrier_activities (linked to carrier_company_id, not establishment_id).
     *
     * @param  array<int, string>|null  $activityIds
     */
    private function syncActivities(CarrierEstablishmentEloquentModel $record, ?array $activityIds): void
    {
        if ($activityIds === null) {
            return;
        }

        $validity = '[2000-01-01,)';

        // Delete existing activities for this carrier_company
        DB::connection('master_v3')->table('core.carrier_activities')
            ->where('tenant_id', $record->tenant_id)
            ->where('carrier_company_id', $record->carrier_company_id)
            ->delete();

        // Insert new activities
        $rows = [];
        foreach ($activityIds as $activityId) {
            $rows[] = [
                'id' => Str::uuid()->toString(),
                'tenant_id' => $record->tenant_id,
                'carrier_company_id' => $record->carrier_company_id,
                'activity_id' => $activityId,
                'validity' => $validity,
                'is_primary' => false,
            ];
        }

        if (! empty($rows)) {
            DB::connection('master_v3')->table('core.carrier_activities')->insert($rows);
        }
    }
}
