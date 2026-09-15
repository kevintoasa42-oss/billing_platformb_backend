<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\ThirdParty\Domain\Mappers\ThirdPartyRoleMapperInterface;
use App\Context\V3\Modules\Core\ThirdParty\Domain\Models\ThirdPartyRole;
use App\Context\V3\Modules\Core\ThirdParty\Infrastructure\Laravel\Eloquent\Models\ThirdPartyRoleModel;

class ThirdPartyRoleMapper implements ThirdPartyRoleMapperInterface
{
    public function toDomain(ThirdPartyRoleModel $record): ThirdPartyRole
    {
        return ThirdPartyRole::fromArray([
            'id' => $record->id,
            'tenant_id' => $record->tenant_id,
            'third_party_id' => $record->third_party_id,
            'role' => $record->role,
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

    public function toDatabaseArray(ThirdPartyRole $role): array
    {
        return $role->toArray();
    }
}
