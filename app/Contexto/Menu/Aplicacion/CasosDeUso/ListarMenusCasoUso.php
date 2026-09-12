<?php

namespace App\Contexto\Menu\Aplicacion\CasosDeUso;

use App\Contexto\Menu\Aplicacion\DTOs\MenuDTO;
use App\Contexto\Menu\Dominio\Repositorios\MenuRepositoryInterface;

class ListarMenusCasoUso
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
