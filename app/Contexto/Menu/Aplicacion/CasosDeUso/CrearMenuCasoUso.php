<?php

namespace App\Contexto\Menu\Aplicacion\CasosDeUso;

use App\Contexto\Menu\Aplicacion\DTOs\MenuDTO;
use App\Contexto\Menu\Dominio\Modelos\Menu;
use App\Contexto\Menu\Dominio\Repositorios\MenuRepositoryInterface;

class CrearMenuCasoUso
{
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
    ) {}

    /**
     * Crea un menu.
     *
     * @param  MenuDTO  $dto
     * @return MenuDTO
     */
    public function ejecutar(MenuDTO $dto): MenuDTO
    {
        $menu = new Menu(
            nombre: $dto->nombre,
            ruta: $dto->ruta,
            icono: $dto->icono,
            parent_id: $dto->parent_id,
            orden: $dto->orden,
        );

        $menu = $this->menuRepository->crear($menu);

        return MenuDTO::fromArray([
            'id' => $menu->id,
            'nombre' => $menu->nombre,
            'ruta' => $menu->ruta,
            'icono' => $menu->icono,
            'parent_id' => $menu->parent_id,
            'orden' => $menu->orden,
        ]);
    }
}
