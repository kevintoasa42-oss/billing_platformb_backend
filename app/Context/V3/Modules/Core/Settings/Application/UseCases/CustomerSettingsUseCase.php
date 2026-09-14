<?php

namespace App\Context\V3\Modules\Core\Settings\Application\UseCases;

use App\Context\V3\Modules\Core\Settings\Application\DTOs\CustomerSettingsDTO;
use App\Context\V3\Modules\Core\Settings\Domain\Models\CustomerSettings;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\CustomerSettingsRepositoryInterface;

class CustomerSettingsUseCase
{
    public function __construct(
        private readonly CustomerSettingsRepositoryInterface $repository,
    ) {}

    public function get(): array
    {
        return $this->repository->get()->toArray();
    }

    public function update(CustomerSettingsDTO $dto): array
    {
        $settings = new CustomerSettings(
            allowMultiplePlates: $dto->allowMultiplePlates,
        );

        return $this->repository->save($settings)->toArray();
    }
}
