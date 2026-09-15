<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Postgres;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyIdentity;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Repository\ThirdPartyAvailabilityRepositoryInterface;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers\ThirdPartyIdentityMapper;

final readonly class EloquentThirdPartyAvailabilityRepository implements ThirdPartyAvailabilityRepositoryInterface
{
    public function __construct(
        private ThirdPartyIdentityMapper $mapper,
    ) {}

    public function findByIdentification(string $identification, string $identificationType): ?ThirdPartyIdentity
    {
        $record = ThirdPartyModel::query()
            ->with('roles')
            ->where('identification_type', $identificationType)
            ->whereRaw("upper(regexp_replace(trim(identification), '[[:space:]]+', '', 'g')) = ?", [$identification])
            ->first();

        return $record === null ? null : $this->mapper->toDomain($record);
    }
}
