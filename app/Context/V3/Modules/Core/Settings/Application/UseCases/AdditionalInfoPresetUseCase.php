<?php

namespace App\Context\V3\Modules\Core\Settings\Application\UseCases;

use App\Context\V3\Modules\Core\Settings\Application\DTOs\AdditionalInfoPresetCreateDTO;
use App\Context\V3\Modules\Core\Settings\Application\DTOs\AdditionalInfoPresetUpdateDTO;
use App\Context\V3\Modules\Core\Settings\Domain\Models\AdditionalInfoPreset;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\AdditionalInfoPresetRepositoryInterface;

class AdditionalInfoPresetUseCase
{
    public function __construct(
        private readonly AdditionalInfoPresetRepositoryInterface $repository,
    ) {}

    public function all(bool $availableOnly = false): array
    {
        return array_map(fn (AdditionalInfoPreset $p) => $p->toArray(), $this->repository->all($availableOnly));
    }

    public function create(AdditionalInfoPresetCreateDTO $dto): array
    {
        return $this->repository->create($dto->toArray())->toArray();
    }

    public function update(int $legacyId, AdditionalInfoPresetUpdateDTO $dto): ?array
    {
        $preset = $this->repository->update($legacyId, $dto->toArray());

        return $preset?->toArray();
    }

    public function delete(int $legacyId): bool
    {
        return $this->repository->delete($legacyId);
    }
}
