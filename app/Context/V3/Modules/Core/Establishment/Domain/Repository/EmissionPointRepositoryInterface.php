<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Repository;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\EmissionPoint;

interface EmissionPointRepositoryInterface
{
    public function all(): array;

    public function find(string $id): ?EmissionPoint;

    public function byEstablishment(string $establishmentId): array;

    public function create(EmissionPoint $emissionPoint): EmissionPoint;

    public function update(string $id, EmissionPoint $emissionPoint): ?EmissionPoint;

    /**
     * Issuance-point surface — legacy_id based operations.
     */
    public function findByLegacyId(int $legacyId): ?EmissionPoint;

    public function deleteByLegacyId(int $legacyId): bool;

    /**
     * @return EmissionPoint[]
     */
    public function byBranchLegacyId(int $branchLegacyId): array;

    public function createForBranch(int $branchLegacyId, array $data): ?EmissionPoint;

    public function updateByLegacyId(int $legacyId, array $data): ?EmissionPoint;

    /**
     * @return array{sequential_number: int, sequential: string, document_number: string}|null
     */
    public function nextSequential(int $branchLegacyId, int $pointLegacyId): ?array;
}
