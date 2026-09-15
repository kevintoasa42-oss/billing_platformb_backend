<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

interface ThirdPartyFieldValueRepositoryInterface
{
    /**
     * Validates and persists values for the just-created third party.
     *
     * @param  list<string>  $roles
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function replace(string $thirdPartyId, array $roles, array $values): array;
}
