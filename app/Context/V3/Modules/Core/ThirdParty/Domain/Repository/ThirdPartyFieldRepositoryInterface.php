<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

interface ThirdPartyFieldRepositoryInterface
{
    /**
     * @return list<array<string, mixed>>
     */
    public function definitions(string $tenantId, ?string $scope = null, bool $activeOnly = true): array;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function createDefinition(string $tenantId, array $input, ?string $actorId = null): array;

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>|null
     */
    public function updateDefinition(string $tenantId, string $id, array $input, ?string $actorId = null): ?array;

    /**
     * @return array<string, mixed>|null
     */
    public function deactivateDefinition(string $tenantId, string $id, ?string $actorId = null): ?array;

    /**
     * @return array<string, mixed>
     */
    public function values(string $tenantId, string $thirdPartyId): array;

    /**
     * @param  list<string>  $thirdPartyIds
     * @return array<string, array<string, mixed>>
     */
    public function valuesByThirdPartyIds(string $tenantId, array $thirdPartyIds): array;

    /**
     * Validate and replace the supplied values. Omitted values are retained on
     * update; null explicitly clears an optional value.
     *
     * @param  array<string, mixed>  $values
     * @return array<string, mixed>
     */
    public function replaceValues(string $tenantId, string $thirdPartyId, array $values, ?string $actorId = null): array;
}
