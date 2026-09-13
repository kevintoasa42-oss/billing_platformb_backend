<?php

namespace App\Context\V1\Modules\Menu\Application\UseCases;

use App\Context\V1\Modules\Menu\Application\DTOs\MenuDTO;
use App\Context\V1\Modules\Menu\Domain\Models\Menu;
use App\Context\V1\Modules\Menu\Domain\Repositories\MenuRepositoryInterface;

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
