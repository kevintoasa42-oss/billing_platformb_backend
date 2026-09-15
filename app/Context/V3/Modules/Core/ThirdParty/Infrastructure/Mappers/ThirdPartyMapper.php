<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers\ThirdPartyMapperInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;

class ThirdPartyMapper implements ThirdPartyMapperInterface
{
    public function toDomain(ThirdPartyModel $record): ThirdParty
    {
        $roles = null;
        $activities = null;

        if ($record->relationLoaded('roles')) {
            $roles = $record->roles->pluck('role')->all();
        }

        if ($record->relationLoaded('activities')) {
            $activities = $record->activities->map(fn ($a) => [
                'establishment_code' => $a->establishment_code,
                'activity_id' => $a->activity_id,
                'validity' => $a->validity,
            ])->all();
        }

        return ThirdParty::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'name' => $record->name,
            'identification' => $record->identification,
            'must_invoice' => $record->must_invoice,
            'legacy_id' => $record->legacy_id,
            'identification_type' => $record->identification_type,
            'address' => $record->address,
            'phone' => $record->phone,
            'email' => $record->email,
            'customer_type_id' => $record->customer_type_id,
            'is_active' => $record->is_active,
            'roles' => $roles,
            'activities' => $activities,
            'custom_fields' => $record->getAttribute('custom_fields'),
        ]);
    }

    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    public function toDatabaseArray(ThirdParty $thirdParty): array
    {
        $identification = $thirdParty->identification === null
            ? null
            : CanonicalIdentification::normalize($thirdParty->identification);
        $data = [
            'name' => $thirdParty->name,
            'identification' => $identification,
            'must_invoice' => $thirdParty->mustInvoice ?? true,
            'identification_type' => $thirdParty->identificationType,
            'address' => $thirdParty->address,
            'phone' => $thirdParty->phone,
            'email' => $thirdParty->email,
            'customer_type_id' => $thirdParty->customerTypeId,
            'is_active' => $thirdParty->isActive ?? true,
        ];

        if ($thirdParty->id !== null) {
            $data['id'] = $thirdParty->id;
        }

        if ($thirdParty->tenantId !== null) {
            $data['tenant_id'] = $thirdParty->tenantId;
        }

        if ($thirdParty->legacyId !== null) {
            $data['legacy_id'] = $thirdParty->legacyId;
        }

        return $data;
    }
}
