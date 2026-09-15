<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;

/**
 * Read/update port for the ThirdParty directory.
 */
interface ThirdPartyQueryRepositoryInterface
{
    /** @return array<int, ThirdParty> */
    public function all(): array;

    public function find(string $id): ?ThirdParty;

    public function findByIdentification(string $identification, ?string $identificationType = null): ?ThirdParty;

    public function update(string $id, ThirdParty $thirdParty): ?ThirdParty;

    /** @return array<int, ThirdParty> */
    public function findByRole(string $role): array;

    /** @return array<int, ThirdParty> */
    public function findCarriers(): array;
}
