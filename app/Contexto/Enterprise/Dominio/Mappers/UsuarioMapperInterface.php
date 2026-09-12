<?php

namespace App\Contexto\Enterprise\Dominio\Mappers;

use App\Contexto\Enterprise\Dominio\Modelos\Usuario;

interface UsuarioMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return Usuario
     */
    public function toDomain(array $data): Usuario;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  Usuario  $usuario
     * @return array
     */
    public function toEloquent(Usuario $usuario): array;
}
