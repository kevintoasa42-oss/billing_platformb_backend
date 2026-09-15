<?php

namespace App\Context\V3\Modules\Core\Settings\Application\UseCases;

use App\Context\V3\Modules\Core\Settings\Application\DTOs\PaymentMethodUpdateDTO;
use App\Context\V3\Modules\Core\Settings\Domain\Models\PaymentMethod;
use App\Context\V3\Modules\Core\Settings\Domain\Repository\PaymentMethodSettingsRepositoryInterface;

class PaymentMethodSettingsUseCase
{
    public function __construct(
        private readonly PaymentMethodSettingsRepositoryInterface $repository,
    ) {}

    public function all(): array
    {
        return array_map(fn (PaymentMethod $m) => $m->toArray(), $this->repository->all());
    }

    public function update(string $code, PaymentMethodUpdateDTO $dto): array
    {
        return $this->repository->update($code, $dto->alias, $dto->isActive)->toArray();
    }
}
