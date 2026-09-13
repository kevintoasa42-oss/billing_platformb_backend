<?php

namespace App\Context\Enterprise\Domain\Mappers;

use App\Context\Enterprise\Domain\Models\User;

interface UserMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return User
     */
    public function toDomain(array $data): User;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  User  $user
     * @return array
     */
    public function toEloquent(User $user): array;
}
