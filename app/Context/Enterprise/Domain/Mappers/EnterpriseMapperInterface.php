<?php

namespace App\Context\Enterprise\Domain\Mappers;

use App\Context\Enterprise\Domain\Models\Enterprise;

interface EnterpriseMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return Enterprise
     */
    public function toDomain(array $data): Enterprise;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  Enterprise  $enterprise
     * @return array
     */
    public function toEloquent(Enterprise $enterprise): array;
}
