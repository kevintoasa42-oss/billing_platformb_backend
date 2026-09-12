<?php

namespace App\Contexto\Enterprise\Dominio\Mappers;

use App\Contexto\Enterprise\Dominio\Modelos\Rol;

interface RolMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return Rol
     */
    public function toDomain(array $data): Rol;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  Rol  $rol
     * @return array
     */
    public function toEloquent(Rol $rol): array;
}
