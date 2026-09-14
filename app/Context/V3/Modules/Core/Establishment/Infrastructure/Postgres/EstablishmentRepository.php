<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EstablishmentRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers\EstablishmentMapper;
use App\Context\V3\Modules\Core\Company\Infrastructure\Laravel\Eloquent\Models\CompanyModel;
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
            ->with('emissionPoints')
            ->orderBy('name')
            ->get();

        return $this->mapper->toBranchDomainList($records);
    }

    public function findByLegacyId(int $legacyId): ?Establishment
    {
        $record = EstablishmentModel::query()
            ->with('emissionPoints')
            ->where('legacy_id', $legacyId)
            ->first();

        return $record !== null ? $this->mapper->toBranchDomain($record) : null;
    }

    public function deleteByLegacyId(int $legacyId): bool
    {
        $branch = EstablishmentModel::query()->where('legacy_id', $legacyId)->first();

        if ($branch === null) {
            return false;
        }

        return (bool) $branch->update(['is_active' => false]);
    }

    public function sriCodeExists(string $sriCode): bool
    {
        return EstablishmentModel::query()->where('sri_code', $sriCode)->exists();
    }

    public function getTenantCompanyId(): ?string
    {
        $company = CompanyModel::query()->first(['id']);

        return $company?->id;
    }

    public function createBranch(array $data): ?Establishment
    {
        $companyId = $this->getTenantCompanyId();

        if ($companyId === null) {
            return null;
        }

        $sriCode = $data['sri_code'];
        $branchCode = $data['branch_code'] ?? $sriCode;

        return DB::connection('master_v3')->transaction(function () use ($companyId, $sriCode, $branchCode, $data): ?Establishment {
            $record = EstablishmentModel::query()->create([
                'company_id' => $companyId,
                'sri_code' => $sriCode,
                'branch_code' => $branchCode,
                'name' => trim($data['name'] ?? 'Sucursal'),
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'is_active' => true,
            ]);

            if (isset($data['issuance_point']) && $data['issuance_point'] !== []) {
                $ip = $data['issuance_point'];
                $pointCode = $this->normalizeCode((string) ($ip['issuance_point_number'] ?? '001'));

                EmissionPointModel::query()->create([
                    'establishment_id' => $record->id,
                    'sri_code' => $pointCode,
                    'name' => $ip['name'] ?? null,
                    'is_active' => (bool) ($ip['is_active'] ?? true),
                    'is_default' => (bool) ($ip['is_default'] ?? false),
                    'has_tax_validity' => (bool) ($ip['has_tax_validity'] ?? true),
                ]);
            }

            return $this->mapper->toBranchDomain($record->fresh('emissionPoints'));
        });
    }

    public function updateBranch(int $legacyId, array $data): ?Establishment
    {
        $record = EstablishmentModel::query()
            ->with('emissionPoints')
            ->where('legacy_id', $legacyId)
            ->first();

        if ($record === null) {
            return null;
        }

        return DB::connection('master_v3')->transaction(function () use ($record, $data): ?Establishment {
            $values = array_filter([
                'name' => $data['name'] ?? null,
                'branch_code' => $data['branch_code'] ?? null,
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'city_id' => $data['city_id'] ?? null,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            ], fn ($value): bool => $value !== null);

            if ($values !== []) {
                $record->update($values);
            }

            if (isset($data['issuance_point']) && $data['issuance_point'] !== []) {
                $point = $record->emissionPoints->first();

                if ($point !== null) {
                    $pointValues = array_filter([
                        'name' => $data['issuance_point']['name'] ?? null,
                        'is_active' => array_key_exists('is_active', $data['issuance_point']) ? (bool) $data['issuance_point']['is_active'] : null,
                        'is_default' => array_key_exists('is_default', $data['issuance_point']) ? (bool) $data['issuance_point']['is_default'] : null,
                        'has_tax_validity' => array_key_exists('has_tax_validity', $data['issuance_point']) ? (bool) $data['issuance_point']['has_tax_validity'] : null,
                    ], fn ($value): bool => $value !== null);

                    if ($pointValues !== []) {
                        $point->update($pointValues);
                    }
                }
            }

            return $this->mapper->toBranchDomain($record->fresh('emissionPoints'));
        });
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

    private function normalizeCode(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?: '001';

        return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
    }
}
