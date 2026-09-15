<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;

/**
 * Coordinates configurable custom field definitions per tenant.
 */
final class ThirdPartyFieldDefinitionUseCase
{
    public function __construct(
        private readonly ThirdPartyFieldDefinitionRepositoryInterface $repository,
    ) {}

    /** @return array<int, ThirdPartyFieldDefinition> */
    public function all(?string $scope, bool $activeOnly = true): array
    {
        return $this->repository->all($scope, $activeOnly);
    }

    public function create(ThirdPartyFieldDefinitionCreateDTO $dto): ThirdPartyFieldDefinition
    {
        return $this->repository->create($dto->toArray());
    }

    public function update(string $id, ThirdPartyFieldDefinitionUpdateDTO $dto): ?ThirdPartyFieldDefinition
    {
        return $this->repository->update($id, $dto->toArray());
    }

    public function deactivate(string $id): ?ThirdPartyFieldDefinition
    {
        return $this->repository->deactivate($id);
    }
}
