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
            nombre: $data['nombre'] ?? null,
            descripcion: $data['descripcion'] ?? null,
        );
    }

    public function toEloquent(Role $rol): array
    {
        return [
            'nombre' => $rol->nombre,
            'descripcion' => $rol->descripcion,
        ];
    }
}
