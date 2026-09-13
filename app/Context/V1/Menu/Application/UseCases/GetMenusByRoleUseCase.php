<?php

namespace App\Context\V1\Menu\Application\UseCases;

use App\Context\V1\Enterprise\Domain\Repositories\RoleRepositoryInterface;
use App\Context\V1\Menu\Application\DTOs\MenuDTO;
use App\Context\V1\Menu\Domain\Repositories\MenuRepositoryInterface;

class GetMenusByRoleUseCase
{
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
        private RoleRepositoryInterface $rolRepository,
    ) {}

    /**
     * Obtiene los menus asignados al rol del user autenticado.
     *
     * @param  int  $userId
     * @return MenuDTO[]
     */
    public function ejecutar(int $userId): array
    {
        // Obtener los roles del user.
        $roles = $this->rolRepository->listarPorUser($userId);

        if (empty($roles)) {
            return [];
        }

        // Por ahora, obtener los menus del primer rol del user.
        // Si un user tiene multiples roles, se pueden combinar los menus.
        $rolId = $roles[0]->id;

        $menus = $this->menuRepository->obtenerPorRol($rolId);

        return array_map(
            fn ($m) => $this->convertirADTO($m->id, $m->name, $m->route, $m->icon, $m->parent_id, $m->order, $m->hijos),
            $menus
        );
    }

    /**
     * Convierte un Menu de dominio a MenuDTO recursivamente.
     */
    private function convertirADTO(?int $id, ?string $name, ?string $route, ?string $icon, ?int $parent_id, int $order, array $hijos = []): MenuDTO
    {
        return new MenuDTO(
            id: $id,
            name: $name,
            route: $route,
            icon: $icon,
            parent_id: $parent_id,
            order: $order,
            hijos: array_map(
                fn ($h) => $this->convertirADTO($h->id, $h->name, $h->route, $h->icon, $h->parent_id, $h->order, $h->hijos ?? []),
                $hijos
            ),
        );
    }
}
