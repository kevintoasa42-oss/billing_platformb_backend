<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyRole;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyRoleModel;

interface ThirdPartyRoleMapperInterface
{
    public function toDomain(ThirdPartyRoleModel $record): ThirdPartyRole;

    /**
     * @param  iterable<ThirdPartyRoleModel>  $records
     * @return array<int, ThirdPartyRole>
     */
    public function toDomainList(iterable $records): array;

    /** @return array<string, mixed> */
    public function toDatabaseArray(ThirdPartyRole $role): array;
}
