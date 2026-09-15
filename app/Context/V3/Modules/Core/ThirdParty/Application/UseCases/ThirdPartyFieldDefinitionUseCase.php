<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Application\UseCases;

use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionCreateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Application\DTOs\ThirdPartyFieldDefinitionUpdateDTO;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyFieldDefinition;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyFieldDefinitionRepositoryInterface;

final readonly class ThirdPartyFieldDefinitionUseCase
{
    public function __construct(
        private ThirdPartyFieldDefinitionRepositoryInterface $repository,
    ) {}

    /** @return list<ThirdPartyFieldDefinition> */
    public function all(?string $scope, bool $activeOnly): array
    {
        return $this->repository->all($scope, $activeOnly);
    }

    public function create(ThirdPartyFieldDefinitionCreateDTO $dto): ThirdPartyFieldDefinition
    {
        return $this->repository->create($dto->toDatabaseArray());
    }

    public function update(string $id, ThirdPartyFieldDefinitionUpdateDTO $dto): ?ThirdPartyFieldDefinition
    {
        return $this->repository->update($id, $dto->toDatabaseArray());
    }

    public function deactivate(string $id): ?ThirdPartyFieldDefinition
    {
        return $this->repository->deactivate($id);
    }
}
