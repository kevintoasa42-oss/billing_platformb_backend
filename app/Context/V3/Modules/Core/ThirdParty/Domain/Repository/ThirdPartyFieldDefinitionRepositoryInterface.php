<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Repository;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;

interface ThirdPartyFieldDefinitionRepositoryInterface
{
    /** @return array<int, ThirdPartyFieldDefinition> */
    public function all(?string $scope, bool $activeOnly): array;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): ThirdPartyFieldDefinition;

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): ?ThirdPartyFieldDefinition;

    public function deactivate(string $id): ?ThirdPartyFieldDefinition;
}
