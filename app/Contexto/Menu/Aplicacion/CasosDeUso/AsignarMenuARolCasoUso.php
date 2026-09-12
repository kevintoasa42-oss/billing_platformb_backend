<?php

namespace App\Contexto\Menu\Aplicacion\CasosDeUso;

use App\Contexto\Menu\Dominio\Repositorios\MenuRepositoryInterface;

class AsignarMenuARolCasoUso
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
