<?php

namespace App\Context\V3\Modules\Core\Establishment\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;
use App\Context\V3\Modules\Core\Establishment\Domain\Repository\EmissionPointRepositoryInterface;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EmissionPointModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Laravel\Eloquent\Models\EstablishmentModel;
use App\Context\V3\Modules\Core\Establishment\Infrastructure\Mappers\EmissionPointMapper;
use App\Context\V3\Modules\Fiscal\Infrastructure\Laravel\Eloquent\Models\SequenceModel;

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
        $record = EmissionPointModel::query()
            ->with('establishment')
            ->where('legacy_id', $legacyId)
            ->first();

        return $record !== null ? $this->mapper->toBranchDomain($record) : null;
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
            ->with('establishment')
            ->whereHas('establishment', function ($query) use ($branchLegacyId): void {
                $query->where('legacy_id', $branchLegacyId);
            })
            ->orderBy('legacy_id')
            ->get();

        return $this->mapper->toBranchDomainList($records);
    }

    public function createForBranch(int $branchLegacyId, array $data): ?EmissionPoint
    {
        $branch = EstablishmentModel::query()
            ->where('legacy_id', $branchLegacyId)
            ->first();

        if ($branch === null) {
            return null;
        }

        $code = $this->mapper->code((string) ($data['issuance_point_number'] ?? '001'));

        $record = EmissionPointModel::query()->create([
            'establishment_id' => $branch->id,
            'sri_code' => $code,
            'name' => $data['name'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_default' => (bool) ($data['is_default'] ?? false),
            'has_tax_validity' => (bool) ($data['has_tax_validity'] ?? true),
        ]);

        $this->seedSequences($record, $data['initial_sequences'] ?? []);

        return $this->mapper->toBranchDomain($record->fresh('establishment'));
    }

    /**
     * Initialize fiscal.sequences rows for a freshly created emission point.
     *
     * @param  array<int, array{document_type: string, last_number: int}>  $initialSequences
     */
    private function seedSequences(EmissionPointModel $point, array $initialSequences): void
    {
        // Default: seed invoice starting at 1 (last_number=0 → next=1) so the
        // editor always has a sequential to show even without explicit input.
        $rows = $initialSequences !== []
            ? $initialSequences
            : [['document_type' => 'invoice', 'last_number' => 0]];

        foreach ($rows as $row) {
            SequenceModel::query()->firstOrCreate(
                [
                    'tenant_id' => $point->tenant_id,
                    'emission_point_id' => $point->id,
                    'document_type' => $row['document_type'],
                ],
                [
                    'environment' => 'lab',
                    'last_number' => (int) ($row['last_number'] ?? 0),
                ],
            );
        }
    }

    public function updateByLegacyId(int $legacyId, array $data): ?EmissionPoint
    {
        $record = EmissionPointModel::query()
            ->with('establishment')
            ->where('legacy_id', $legacyId)
            ->first();

        if ($record === null) {
            return null;
        }

        $values = array_filter([
            'name' => $data['name'] ?? null,
            'sri_code' => isset($data['issuance_point_number']) ? $this->mapper->code((string) $data['issuance_point_number']) : null,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : null,
            'is_default' => array_key_exists('is_default', $data) ? (bool) $data['is_default'] : null,
            'has_tax_validity' => array_key_exists('has_tax_validity', $data) ? (bool) $data['has_tax_validity'] : null,
        ], fn ($value): bool => $value !== null);

        if ($values !== []) {
            $record->update($values);
        }

        if (isset($data['sequences']) && is_array($data['sequences'])) {
            $this->syncSequences($record, $data['sequences']);
        }

        return $this->mapper->toBranchDomain($record->fresh('establishment'));
    }

    /**
     * Update or create fiscal.sequences rows for an existing emission point.
     *
     * @param  array<int, array{document_type: string, last_number: int}>  $sequences
     */
    private function syncSequences(EmissionPointModel $point, array $sequences): void
    {
        foreach ($sequences as $row) {
            SequenceModel::query()->updateOrCreate(
                [
                    'tenant_id' => $point->tenant_id,
                    'emission_point_id' => $point->id,
                    'document_type' => $row['document_type'],
                ],
                [
                    'environment' => 'lab',
                    'last_number' => (int) ($row['last_number'] ?? 0),
                ],
            );
        }
    }

    public function nextSequential(int $branchLegacyId, int $pointLegacyId): ?array
    {
        $point = EmissionPointModel::query()
            ->with('establishment')
            ->where('legacy_id', $pointLegacyId)
            ->whereHas('establishment', function ($query) use ($branchLegacyId): void {
                $query->where('legacy_id', $branchLegacyId);
            })
            ->first();

        if ($point === null) {
            return null;
        }

        $last = (int) (SequenceModel::query()
            ->where('emission_point_id', $point->id)
            ->where('document_type', 'invoice')
            ->value('last_number') ?? 0);

        $next = $last + 1;

        return [
            'sequential_number' => $next,
            'sequential' => str_pad((string) $next, 9, '0', STR_PAD_LEFT),
            'document_number' => sprintf('%s-%s-%09d', $point->establishment->sri_code, $point->sri_code, $next),
        ];
    }
}
