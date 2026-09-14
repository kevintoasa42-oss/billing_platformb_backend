<?php

namespace App\Context\V1\Modules\Clients\Enterprise\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Modules\Clients\Enterprise\Domain\Mappers\RoleMapperInterface;
use App\Context\V1\Modules\Clients\Enterprise\Domain\Models\Role;

class RoleMapper implements RoleMapperInterface
{
    public function toDomain(array $data): Role
    {
        return new Role(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function toEloquent(Role $rol): array
    {
        return [
            'name' => $rol->name,
            'description' => $rol->description,
        ];
    }
}
