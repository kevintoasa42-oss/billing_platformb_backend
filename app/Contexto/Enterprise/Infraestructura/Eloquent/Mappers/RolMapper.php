<?php

namespace App\Contexto\Enterprise\Infraestructura\Eloquent\Mappers;

use App\Contexto\Enterprise\Dominio\Mappers\RolMapperInterface;
use App\Contexto\Enterprise\Dominio\Modelos\Rol;

class RolMapper implements RolMapperInterface
{
    public function toDomain(array $data): Rol
    {
        return new Rol(
            id: $data['id'] ?? null,
            nombre: $data['nombre'] ?? null,
            descripcion: $data['descripcion'] ?? null,
        );
    }

    public function toEloquent(Rol $rol): array
    {
        return [
            'nombre' => $rol->nombre,
            'descripcion' => $rol->descripcion,
        ];
    }
}
