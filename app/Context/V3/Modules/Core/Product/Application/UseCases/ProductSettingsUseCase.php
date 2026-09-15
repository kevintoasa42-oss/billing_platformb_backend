<?php

namespace App\Context\V3\Modules\Core\Product\Application\UseCases;

use App\Context\V3\Modules\Core\Product\Application\DTOs\ProductSettingsDTO;
use App\Context\V3\Modules\Core\Product\Domain\Models\ProductSettings;
use App\Context\V3\Modules\Core\Product\Domain\Repository\ProductSettingsRepositoryInterface;

class ProductSettingsUseCase
{
    public function __construct(
        private readonly ProductSettingsRepositoryInterface $repository,
    ) {}

    public function get(): ProductSettings
    {
        return $this->repository->get();
    }

    public function update(ProductSettingsDTO $dto): ProductSettings
    {
        $settings = new ProductSettings(
            allowDuplicateNames: $dto->allowDuplicateNames,
            requireBarcode: false,
            requireAuxiliaryCode: false,
            auxiliaryCodePrefix: $dto->auxiliaryCodePrefix,
            defaultProductType: $dto->defaultProductType,
            defaultIvaTypeId: $dto->defaultIvaTypeId,
            requireDescription: $dto->requireDescription,
        );

        return $this->repository->save($settings);
    }
}
