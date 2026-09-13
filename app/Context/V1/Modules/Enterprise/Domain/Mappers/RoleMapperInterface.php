<?php

namespace App\Context\V1\Modules\Enterprise\Domain\Mappers;

use App\Context\V1\Modules\Enterprise\Domain\Models\Role;

interface RoleMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return Role
     */
    public function toDomain(array $data): Role;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  Role  $rol
     * @return array
     */
    public function toEloquent(Role $rol): array;
}
