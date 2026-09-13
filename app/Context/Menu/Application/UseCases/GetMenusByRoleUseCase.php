<?php

namespace App\Context\Menu\Application\UseCases;

use App\Context\Enterprise\Domain\Repositories\RoleRepositoryInterface;
use App\Context\Menu\Application\DTOs\MenuDTO;
use App\Context\Menu\Domain\Repositories\MenuRepositoryInterface;

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
            fn ($m) => $this->convertirADTO($m->id, $m->nombre, $m->ruta, $m->icono, $m->parent_id, $m->orden, $m->hijos),
            $menus
        );
    }

    /**
     * Convierte un Menu de dominio a MenuDTO recursivamente.
     */
    private function convertirADTO(?int $id, ?string $nombre, ?string $ruta, ?string $icono, ?int $parent_id, int $orden, array $hijos = []): MenuDTO
    {
        return new MenuDTO(
            id: $id,
            nombre: $nombre,
            ruta: $ruta,
            icono: $icono,
            parent_id: $parent_id,
            orden: $orden,
            hijos: array_map(
                fn ($h) => $this->convertirADTO($h->id, $h->nombre, $h->ruta, $h->icono, $h->parent_id, $h->orden, $h->hijos ?? []),
                $hijos
            ),
        );
    }
}
