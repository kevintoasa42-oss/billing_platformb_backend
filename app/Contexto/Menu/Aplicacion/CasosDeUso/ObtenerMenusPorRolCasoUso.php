<?php

namespace App\Contexto\Menu\Aplicacion\CasosDeUso;

use App\Contexto\Enterprise\Dominio\Repositorios\RolRepositoryInterface;
use App\Contexto\Menu\Aplicacion\DTOs\MenuDTO;
use App\Contexto\Menu\Dominio\Repositorios\MenuRepositoryInterface;

class ObtenerMenusPorRolCasoUso
{
    public function __construct(
        private MenuRepositoryInterface $menuRepository,
        private RolRepositoryInterface $rolRepository,
    ) {}

    /**
     * Obtiene los menus asignados al rol del usuario autenticado.
     *
     * @param  int  $usuarioId
     * @return MenuDTO[]
     */
    public function ejecutar(int $usuarioId): array
    {
        // Obtener los roles del usuario.
        $roles = $this->rolRepository->listarPorUsuario($usuarioId);

        if (empty($roles)) {
            return [];
        }

        // Por ahora, obtener los menus del primer rol del usuario.
        // Si un usuario tiene multiples roles, se pueden combinar los menus.
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
