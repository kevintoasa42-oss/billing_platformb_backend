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
            name: $dto->name,
            route: $dto->route,
            icon: $dto->icon,
            parent_id: $dto->parent_id,
            order: $dto->order,
        );

        $menu = $this->menuRepository->crear($menu);

        return MenuDTO::fromArray([
            'id' => $menu->id,
            'name' => $menu->name,
            'route' => $menu->route,
            'icon' => $menu->icon,
            'parent_id' => $menu->parent_id,
            'order' => $menu->order,
        ]);
    }
}
