<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyIdentity;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;

final class ThirdPartyIdentityMapper
{
    public function toDomain(ThirdPartyModel $model): ThirdPartyIdentity
    {
        return new ThirdPartyIdentity(
            id: (string) $model->getKey(),
            name: (string) $model->getAttribute('name'),
            roles: $model->roles
                ->pluck('role')
                ->map(static fn (mixed $role): string => (string) $role)
                ->values()
                ->all(),
        );
    }
}
