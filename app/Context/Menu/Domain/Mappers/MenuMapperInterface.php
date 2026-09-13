<?php

namespace App\Context\Menu\Domain\Mappers;

use App\Context\Menu\Domain\Models\Menu;

interface MenuMapperInterface
{
    /**
     * Convierte un array de datos (Eloquent) a un modelo de dominio.
     *
     * @param  array  $data
     * @return Menu
     */
    public function toDomain(array $data): Menu;

    /**
     * Convierte un modelo de dominio a un array para Eloquent.
     *
     * @param  Menu  $menu
     * @return array
     */
    public function toEloquent(Menu $menu): array;
}
