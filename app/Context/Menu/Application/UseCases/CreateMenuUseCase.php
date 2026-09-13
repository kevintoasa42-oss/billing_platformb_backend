<?php

namespace App\Context\Menu\Application\UseCases;

use App\Context\Menu\Application\DTOs\MenuDTO;
use App\Context\Menu\Domain\Models\Menu;
use App\Context\Menu\Domain\Repositories\MenuRepositoryInterface;

class CreateMenuUseCase
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
