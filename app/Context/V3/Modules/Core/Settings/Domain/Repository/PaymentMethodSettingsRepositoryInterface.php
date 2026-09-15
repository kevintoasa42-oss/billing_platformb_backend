<?php

namespace App\Context\V3\Modules\Core\Settings\Domain\Repository;

use App\Context\V3\Modules\Core\Settings\Domain\Models\PaymentMethod;

interface PaymentMethodSettingsRepositoryInterface
{
    /**
     * @return PaymentMethod[]
     */
    public function all(): array;

    public function update(string $code, ?string $alias, ?bool $isActive): PaymentMethod;
}
