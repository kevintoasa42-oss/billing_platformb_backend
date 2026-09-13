<?php

namespace App\Contexto\Product\Dominio\Mappers;

use App\Contexto\Product\Dominio\Modelos\Producto;

interface ProductoMapperInterface
{
    public function toDomain(object $model): Producto;

    public function toModel(Producto $producto): array;
}
