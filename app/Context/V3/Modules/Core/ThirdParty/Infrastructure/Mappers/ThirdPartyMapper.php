<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;

final class ThirdPartyMapper
{
    public function toDomain(ThirdPartyModel $model): ThirdParty
    {
        return ThirdParty::fromArray([
            'id' => (string) $model->getKey(),
            'tenant_id' => (string) $model->getAttribute('tenant_id'),
            'name' => (string) $model->getAttribute('name'),
            'identification' => (string) $model->getAttribute('identification'),
            'identification_type' => (string) $model->getAttribute('identification_type'),
            'person_type' => $model->getAttribute('person_type'),
            'must_invoice' => (bool) $model->getAttribute('must_invoice'),
            'legacy_id' => $model->getAttribute('legacy_id'),
            'address' => $model->getAttribute('address'),
            'phone' => $model->getAttribute('phone'),
            'email' => $model->getAttribute('email'),
            'customer_type_id' => $model->getAttribute('customer_type_id'),
            'is_active' => (bool) $model->getAttribute('is_active'),
            'roles' => $model->relationLoaded('roles')
                ? $model->roles
                    ->pluck('role')
                    ->map(static fn (mixed $role): string => (string) $role)
                    ->values()
                    ->all()
                : [],
        ]);
    }

    /** @return array<string, mixed> */
    public function toDatabaseArray(ThirdParty $thirdParty): array
    {
        return [
            'name' => $thirdParty->name,
            'identification' => $thirdParty->identification,
            'identification_type' => $thirdParty->identificationType,
            'person_type' => $thirdParty->personType,
            'must_invoice' => $thirdParty->mustInvoice,
            'legacy_id' => $thirdParty->legacyId,
            'address' => $thirdParty->address,
            'phone' => $thirdParty->phone,
            'email' => $thirdParty->email,
            'customer_type_id' => $thirdParty->customerTypeId,
            'is_active' => $thirdParty->isActive,
        ];
    }
}
