<?php

namespace App\Context\V1\Modules\Menu\Domain\Repositories;

use App\Context\V1\Modules\Menu\Domain\Models\Menu;

interface MenuRepositoryInterface
{
    /**
     * Crea un menu y devuelve el modelo de dominio con el id asignado.
     *
     * @param  Menu  $menu
     * @return Menu
     */
    public function crear(Menu $menu): Menu;

    /**
     * Lista todos los menus (con jerarquia).
     *
     * @return Menu[]
     */
    public function listar(): array;

    /**
     * Busca un menu por su id.
     *
     * @param  int  $id
     * @return Menu|null
     */
    public function buscarPorId(int $id): ?Menu;

    /**
     * Asigna un menu a un rol.
     *
     * @param  int  $menuId
     * @param  int  $rolId
     * @return void
     */
    public function asignarARol(int $menuId, int $rolId): void;

    /**
     * Obtiene los menus asignados a un rol (con jerarquia).
     *
     * @param  int  $rolId
     * @return Menu[]
     */
    public function obtenerPorRol(int $rolId): array;
}
