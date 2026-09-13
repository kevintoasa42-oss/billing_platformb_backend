<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Mappers;

use App\Context\Enterprise\Domain\Mappers\RoleMapperInterface;
use App\Context\Enterprise\Domain\Models\Role;

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
