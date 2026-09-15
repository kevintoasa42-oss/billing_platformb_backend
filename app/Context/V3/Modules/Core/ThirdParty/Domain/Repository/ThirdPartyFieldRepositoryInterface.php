<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

interface ThirdPartyFieldRepositoryInterface
{
    /** @return array<string, mixed> */
    public function values(string $tenantId, string $thirdPartyId): array;

    /** @param list<string> $thirdPartyIds @return array<string, array<string, mixed>> */
    public function valuesByThirdPartyIds(string $tenantId, array $thirdPartyIds): array;

    /** @param array<string, mixed> $values @return array<string, mixed> */
    public function replaceValues(string $tenantId, string $thirdPartyId, array $values, ?string $actorId = null): array;
}
