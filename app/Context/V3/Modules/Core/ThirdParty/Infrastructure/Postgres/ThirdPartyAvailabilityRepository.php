<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyIdentity;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\ValueObjects\CanonicalIdentification;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;

final class ThirdPartyAvailabilityRepository implements ThirdPartyAvailabilityRepositoryInterface
{
    public function findByIdentification(string $identification, string $identificationType): ?ThirdPartyIdentity
    {
        $canonical = CanonicalIdentification::normalize($identification);
        if ($canonical === '') {
            return null;
        }

        $record = ThirdPartyModel::query()->with('roles')
            ->whereRaw("upper(regexp_replace(trim(identification), '\\s+', '', 'g')) = ?", [$canonical])
            ->where('identification_type', CanonicalIdentification::normalizeType($identificationType, $canonical))
            ->first();

        if ($record === null) {
            return null;
        }

        return new ThirdPartyIdentity(
            (string) $record->id,
            (string) $record->name,
            $record->relationLoaded('roles')
                ? $record->roles->map(static fn ($role) => (string) $role->role)->values()->all()
                : [],
        );
    }
}
