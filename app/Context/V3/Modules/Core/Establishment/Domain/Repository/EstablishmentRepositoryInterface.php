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
}
