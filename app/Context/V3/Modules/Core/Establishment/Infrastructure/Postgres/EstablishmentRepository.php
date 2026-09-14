<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EstablishmentRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers\EstablishmentMapper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EstablishmentRepository implements EstablishmentRepositoryInterface
{
    public function __construct(
        private readonly EstablishmentMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = EstablishmentModel::query()
            ->with('activities')
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?Establishment
    {
        $record = EstablishmentModel::query()
            ->with('activities')
            ->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function create(Establishment $establishment): Establishment
    {
        return DB::connection('master_v3')->transaction(function () use ($establishment): Establishment {
            $record = EstablishmentModel::query()->create(
                $this->mapper->toDatabaseArray($establishment)
            );

            $this->syncActivities($record, $establishment->activityIds);

            return $this->mapper->toDomain($record->load('activities'));
        });
    }

    public function update(string $id, Establishment $establishment): ?Establishment
    {
        return DB::connection('master_v3')->transaction(function () use ($id, $establishment): ?Establishment {
            $record = EstablishmentModel::query()->find($id);
            if ($record === null) {
                return null;
            }

            $record->update($this->mapper->toDatabaseArray($establishment));

            $this->syncActivities($record, $establishment->activityIds);

            return $this->mapper->toDomain($record->load('activities'));
        });
    }

    public function allBranches(): array
    {
        $records = EstablishmentModel::query()
            ->orderBy('name')
            ->get();

        return $records->map(fn ($r): Establishment => $this->toBranchDomain($r))->all();
    }

    public function findByLegacyId(int $legacyId): ?Establishment
    {
        $record = EstablishmentModel::query()->where('legacy_id', $legacyId)->first();

        return $record !== null ? $this->toBranchDomain($record) : null;
    }

    public function deleteByLegacyId(int $legacyId): bool
    {
        return (bool) DB::connection('master_v3')->transaction(function () use ($legacyId): int {
            $branch = EstablishmentModel::query()->where('legacy_id', $legacyId)->first();

            if ($branch === null) {
                return 0;
            }

            return (int) $branch->update(['is_active' => false]);
        });
    }

    /**
     * Build Establishment with nested issuance_points (branch shape).
     */
    private function toBranchDomain(EstablishmentModel $record): Establishment
    {
        $points = DB::connection('master_v3')
            ->table('core.emission_points')
            ->where('establishment_id', $record->id)
            ->orderBy('legacy_id')
            ->get()
            ->map(function ($point) use ($record): array {
                $last = (int) (DB::connection('master_v3')
                    ->table('fiscal.sequences')
                    ->where('emission_point_id', $point->id)
                    ->where('document_type', 'invoice')
                    ->value('last_number') ?? 0);

                return [
                    'id' => (int) $point->legacy_id,
                    'branch_id' => (int) $record->legacy_id,
                    'name' => trim((string) ($point->name ?? '')) !== '' ? $point->name : 'Punto '.$point->sri_code,
                    'issuance_point_number' => (string) $point->sri_code,
                    'is_active' => (bool) $point->is_active,
                    'is_default' => (bool) $point->is_default,
                    'has_tax_validity' => (bool) $point->has_tax_validity,
                    'last_issued_sequential' => $last,
                    'next_sequential' => $last + 1,
                ];
            })
            ->all();

        return new Establishment(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            companyId: (string) $record->company_id,
            sriCode: (string) $record->sri_code,
            name: $record->name,
            legacyId: (int) $record->legacy_id,
            branchCode: $record->branch_code ?? $record->sri_code,
            address: $record->address,
            phone: $record->phone,
            email: $record->email,
            cityId: $record->city_id !== null ? (int) $record->city_id : null,
            isActive: (bool) $record->is_active,
            issuancePoints: $points,
        );
    }

    /**
     * @param  array<int, string>|null  $activityIds
     */
    private function syncActivities(EstablishmentModel $record, ?array $activityIds): void
    {
        if ($activityIds === null) {
            return;
        }

        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id), $activityIds
        ))));

        $connection = DB::connection('master_v3');

        $known = $connection->table('core.economic_activities')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        if (count($known) !== count($ids)) {
            throw new \InvalidArgumentException('One or more economic activities do not exist.');
        }

        $table = $connection->table('core.establishment_activities');

        $current = $table
            ->where('tenant_id', $record->tenant_id)
            ->where('establishment_id', $record->id)
            ->whereRaw('validity @> CURRENT_DATE')
            ->lockForUpdate()
            ->get();

        $currentByActivity = $current->keyBy('activity_id');

        foreach ($current as $row) {
            if (! in_array((string) $row->activity_id, $ids, true)) {
                $table->where('tenant_id', $record->tenant_id)
                    ->where('id', $row->id)
                    ->update(['validity' => DB::raw("daterange(lower(validity), CURRENT_DATE, '[)')")]);
            }
        }

        foreach ($ids as $activityId) {
            if (isset($currentByActivity[$activityId])) {
                continue;
            }

            $table->insert([
                'id' => Str::uuid()->toString(),
                'tenant_id' => $record->tenant_id,
                'establishment_id' => $record->id,
                'activity_id' => $activityId,
                'validity' => DB::raw("daterange(CURRENT_DATE, NULL, '[)')"),
            ]);
        }
    }
}
