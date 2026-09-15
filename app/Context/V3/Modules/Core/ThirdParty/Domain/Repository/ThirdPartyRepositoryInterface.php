<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;

interface ThirdPartyRepositoryInterface
{
    /**
     * @return array<int, ThirdParty>
     */
    public function all(): array;

    public function find(string $id): ?ThirdParty;

    /**
     * Checks the tenant-scoped canonical identification used by the database
     * uniqueness index. The optional id is excluded for updates.
     */
    public function identificationExists(string $identification, ?string $excludeId = null, ?string $identificationType = null): bool;

    public function findByIdentification(string $identification, ?string $identificationType = null): ?ThirdParty;

    public function create(ThirdParty $thirdParty): ThirdParty;

    public function update(string $id, ThirdParty $thirdParty): ?ThirdParty;

    /**
     * @return array<int, ThirdParty>
     */
    public function findByRole(string $role): array;

    /**
     * Transportistas: rol carrier. `must_invoice` es un dato legado y no
     * decide el rol.
     *
     * @return array<int, ThirdParty>
     */
    public function findCarriers(): array;
}
