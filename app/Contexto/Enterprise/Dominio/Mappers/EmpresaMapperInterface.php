<?php

namespace App\Contexto\Enterprise\Dominio\Mappers;

use App\Contexto\Enterprise\Dominio\Modelos\Empresa;

interface EmpresaMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return Empresa
     */
    public function toDomain(array $data): Empresa;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  Empresa  $empresa
     * @return array
     */
    public function toEloquent(Empresa $empresa): array;
}
