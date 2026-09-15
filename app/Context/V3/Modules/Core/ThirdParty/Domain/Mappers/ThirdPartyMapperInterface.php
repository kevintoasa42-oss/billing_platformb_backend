<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdParty;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyModel;

interface ThirdPartyMapperInterface
{
    public function toDomain(ThirdPartyModel $record): ThirdParty;

    /**
     * @param  iterable<ThirdPartyModel>  $records
     * @return array<int, ThirdParty>
     */
    public function toDomainList(iterable $records): array;

    /** @return array<string, mixed> */
    public function toDatabaseArray(ThirdParty $thirdParty): array;
}
