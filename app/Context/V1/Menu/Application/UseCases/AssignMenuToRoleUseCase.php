<?php

namespace App\Context\V1\Menu\Application\UseCases;

use App\Context\V1\Menu\Domain\Repositories\MenuRepositoryInterface;

class AssignMenuToRoleUseCase
{
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
    ) {}

    /**
     * Asigna un menu a un rol.
     *
     * @param  int  $menuId
     * @param  int  $rolId
     * @return void
     */
    public function ejecutar(int $menuId, int $rolId): void
    {
        $this->menuRepository->asignarARol($menuId, $rolId);
    }
}
