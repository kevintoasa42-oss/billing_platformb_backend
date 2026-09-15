<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyActivity;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyActivityModel;

interface ThirdPartyActivityMapperInterface
{
    public function toDomain(ThirdPartyActivityModel $record): ThirdPartyActivity;

    /**
     * @param  iterable<ThirdPartyActivityModel>  $records
     * @return array<int, ThirdPartyActivity>
     */
    public function toDomainList(iterable $records): array;

    /** @return array<string, mixed> */
    public function toDatabaseArray(ThirdPartyActivity $activity): array;
}
