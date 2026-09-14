<?php

namespace App\Context\V3\Modules\Core\Establishment\Domain\Repository;

use App\Context\V3\Modules\Core\Establishment\Domain\Models\Establishment;

interface EstablishmentRepositoryInterface
{
    public function all(): array;

    public function find(string $id): ?Establishment;

    public function create(Establishment $establishment): Establishment;

    public function update(string $id, Establishment $establishment): ?Establishment;

    /**
     * Branch surface — list branches with nested issuance points.
     *
     * @return Establishment[]
     */
    public function allBranches(): array;

    public function findByLegacyId(int $legacyId): ?Establishment;

    public function deleteByLegacyId(int $legacyId): bool;

    /**
     * Check if an establishment with the given SRI code already exists.
     */
    public function sriCodeExists(string $sriCode): bool;

    /**
     * Get the tenant's company ID (first company).
     */
    public function getTenantCompanyId(): ?string;

    /**
     * Create a branch (establishment) with an optional emission point.
     *
     * @param  array<string, mixed>  $data
     */
    public function createBranch(array $data): ?Establishment;

    /**
     * Update a branch (establishment) by legacy ID, with an optional emission point update.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateBranch(int $legacyId, array $data): ?Establishment;
}
