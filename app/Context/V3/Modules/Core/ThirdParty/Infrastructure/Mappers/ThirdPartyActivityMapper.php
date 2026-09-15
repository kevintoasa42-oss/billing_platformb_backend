<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers\ThirdPartyActivityMapperInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyActivity;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyActivityModel;

class ThirdPartyActivityMapper implements ThirdPartyActivityMapperInterface
{
    public function toDomain(ThirdPartyActivityModel $record): ThirdPartyActivity
    {
        return ThirdPartyActivity::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'third_party_id' => $record->third_party_id,
            'establishment_code' => $record->establishment_code,
            'activity_id' => $record->activity_id,
            'validity' => $record->validity,
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

    public function toDatabaseArray(ThirdPartyActivity $activity): array
    {
        return $activity->toArray();
    }
}
