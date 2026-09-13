<?php

namespace App\Context\Menu\Application\UseCases;

use App\Context\Menu\Application\DTOs\MenuDTO;
use App\Context\Menu\Domain\Repositories\MenuRepositoryInterface;

class ListMenusUseCase
{
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
    ) {}

    /**
     * Lista todos los menus (con jerarquia).
     *
     * @return MenuDTO[]
     */
    public function ejecutar(): array
    {
        $menus = $this->menuRepository->listar();

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
