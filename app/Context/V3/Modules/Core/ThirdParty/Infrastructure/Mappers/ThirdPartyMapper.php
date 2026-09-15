<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;

final class ThirdPartyMapper
{
    public function toDomain(ThirdPartyModel $record): ThirdParty
    {
        return new ThirdParty(
            id: (string) $record->id,
            tenantId: (string) $record->tenant_id,
            name: (string) $record->name,
            identification: $record->identification,
            mustInvoice: $record->must_invoice,
            legacyId: $record->legacy_id !== null ? (string) $record->legacy_id : null,
            identificationType: $record->identification_type,
            address: $record->address,
            phone: $record->phone,
            email: $record->email,
            customerTypeId: $record->customer_type_id !== null ? (int) $record->customer_type_id : null,
            isActive: $record->is_active,
            roles: $record->relationLoaded('roles')
                ? $record->roles->map(static fn ($role) => (string) $role->role)->values()->all()
                : null,
            activities: $record->relationLoaded('activities')
                ? $record->activities->map(static fn ($activity): array => [
                    'id' => (string) $activity->id,
                    'establishment_code' => $activity->establishment_code,
                    'activity_id' => $activity->activity_id !== null ? (string) $activity->activity_id : null,
                    'validity' => $activity->validity !== null ? (string) $activity->validity : null,
                ])->values()->all()
                : null,
        );
    }

    /** @param iterable<ThirdPartyModel> $records @return array<int, ThirdParty> */
    public function toDomainList(iterable $records): array
    {
        return array_map(fn (ThirdPartyModel $record): ThirdParty => $this->toDomain($record), is_array($records) ? $records : iterator_to_array($records));
    }

    /** @return array<string, mixed> */
    public function toDatabaseArray(ThirdParty $thirdParty): array
    {
        return [
            'name' => $thirdParty->name,
            'identification' => $thirdParty->identification,
            'identification_type' => $thirdParty->identificationType,
            'person_type' => null,
            'must_invoice' => $thirdParty->mustInvoice ?? true,
            'legacy_id' => $thirdParty->legacyId !== null ? (is_numeric($thirdParty->legacyId) ? (int) $thirdParty->legacyId : null) : null,
            'address' => $thirdParty->address,
            'phone' => $thirdParty->phone,
            'email' => $thirdParty->email,
            'customer_type_id' => $thirdParty->customerTypeId,
            'is_active' => $thirdParty->isActive ?? true,
        ];
    }
}
