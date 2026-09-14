<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers\EmissionPointMapper;
use Illuminate\Support\Facades\DB;

class EmissionPointRepository implements EmissionPointRepositoryInterface
{
    public function __construct(
        private readonly EmissionPointMapper $mapper,
    ) {}

    public function all(): array
    {
        $records = EmissionPointModel::query()
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function find(string $id): ?EmissionPoint
    {
        $record = EmissionPointModel::query()->find($id);

        return $record !== null ? $this->mapper->toDomain($record) : null;
    }

    public function byEstablishment(string $establishmentId): array
    {
        $records = EmissionPointModel::query()
            ->where('establishment_id', $establishmentId)
            ->orderBy('name')
            ->get();

        return $this->mapper->toDomainList($records);
    }

    public function create(EmissionPoint $emissionPoint): EmissionPoint
    {
        $record = EmissionPointModel::query()->create(
            $this->mapper->toDatabaseArray($emissionPoint)
        );

        return $this->mapper->toDomain($record);
    }

    public function update(string $id, EmissionPoint $emissionPoint): ?EmissionPoint
    {
        $record = EmissionPointModel::query()->find($id);

        if ($record === null) {
            return null;
        }

        $record->update($this->mapper->toDatabaseArray($emissionPoint));

        return $this->mapper->toDomain($record->fresh());
    }

    public function findByLegacyId(int $legacyId): ?EmissionPoint
    {
        $record = EmissionPointModel::query()->where('legacy_id', $legacyId)->first();

        return $record !== null ? $this->toBranchDomain($record) : null;
    }

    public function deleteByLegacyId(int $legacyId): bool
    {
        return (bool) EmissionPointModel::query()
            ->where('legacy_id', $legacyId)
            ->update(['is_active' => false]);
    }

    public function byBranchLegacyId(int $branchLegacyId): array
    {
        $records = EmissionPointModel::query()
            ->whereHas('establishment', function ($query) use ($branchLegacyId): void {
                $query->where('legacy_id', $branchLegacyId);
            })
            ->orderBy('legacy_id')
            ->get();

        return $records->map(fn ($r): EmissionPoint => $this->toBranchDomain($r))->all();
    }

    public function createForBranch(int $branchLegacyId, array $data): ?EmissionPoint
    {
        return DB::connection('master_v3')->transaction(function () use ($branchLegacyId, $data): ?EmissionPoint {
            $branch = DB::connection('master_v3')
                ->table('core.establishments')
                ->where('legacy_id', $branchLegacyId)
                ->first();

            if ($branch === null) {
                return null;
            }

            $code = $this->code((string) ($data['issuance_point_number'] ?? '001'));

            $record = EmissionPointModel::query()->create([
                'establishment_id' => $branch->id,
                'sri_code' => $code,
                'name' => $data['name'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'is_default' => (bool) ($data['is_default'] ?? false),
                'has_tax_validity' => (bool) ($data['has_tax_validity'] ?? true),
            ]);

            return $this->toBranchDomain($record->fresh());
        });
    }

    public function updateByLegacyId(int $legacyId, array $data): ?EmissionPoint
    {
        return DB::connection('master_v3')->transaction(function () use ($legacyId, $data): ?EmissionPoint {
            $record = EmissionPointModel::query()->where('legacy_id', $legacyId)->first();

            if ($record === null) {
                return null;
            }

            $values = array_filter([
                'name' => $data['name'] ?? null,
                'sri_code' => isset($data['issuance_point_number']) ? $this->code((string) $data['issuance_point_number']) : null,
                'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
                'is_default' => array_key_exists('is_default', $data) ? (bool) $data['is_default'] : null,
                'has_tax_validity' => array_key_exists('has_tax_validity', $data) ? (bool) $data['has_tax_validity'] : null,
            ], fn ($value): bool => $value !== null);

            if ($values !== []) {
                $record->update($values);
            }

            return $this->toBranchDomain($record->fresh());
        });
    }

    public function nextSequential(int $branchLegacyId, int $pointLegacyId): ?array
    {
        $row = DB::connection('master_v3')->table('core.emission_points as p')
            ->join('core.establishments as e', 'e.id', '=', 'p.establishment_id')
            ->where('p.legacy_id', $pointLegacyId)
            ->where('e.legacy_id', $branchLegacyId)
            ->select(['p.id', 'p.sri_code as point_code', 'e.sri_code as branch_code'])
            ->first();

        if ($row === null) {
            return null;
        }

        $last = (int) (DB::connection('master_v3')
            ->table('fiscal.sequences')
            ->where('emission_point_id', $row->id)
            ->where('document_type', 'invoice')
            ->value('last_number') ?? 0);

        $next = $last + 1;

        return [
            'sequential_number' => $next,
            'sequential' => str_pad((string) $next, 9, '0', STR_PAD_LEFT),
            'document_number' => sprintf('%s-%s-%09d', $row->branch_code, $row->point_code, $next),
        ];
    }

    /**
     * Build EmissionPoint with branch-legacy-id + sequential fields.
     */
    private function toBranchDomain(EmissionPointModel $record): EmissionPoint
    {
        $branchLegacyId = DB::connection('master_v3')
            ->table('core.establishments')
            ->where('id', $record->establishment_id)
            ->value('legacy_id');

        $last = (int) (DB::connection('master_v3')
            ->table('fiscal.sequences')
            ->where('emission_point_id', $record->id)
            ->where('document_type', 'invoice')
            ->value('last_number') ?? 0);

        return new EmissionPoint(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            establishmentId: (string) $record->establishment_id,
            sriCode: (string) $record->sri_code,
            name: $record->name,
            legacyId: (int) $record->legacy_id,
            branchLegacyId: (int) $branchLegacyId,
            isActive: (bool) $record->is_active,
            isDefault: (bool) $record->is_default,
            hasTaxValidity: (bool) $record->has_tax_validity,
            lastIssuedSequential: $last,
            nextSequential: $last + 1,
        );
    }

    private function code(string $value): string
    {
        $digits = preg_replace('/\D+/', '', $value) ?: '001';

        return str_pad(substr($digits, -3), 3, '0', STR_PAD_LEFT);
    }
}
