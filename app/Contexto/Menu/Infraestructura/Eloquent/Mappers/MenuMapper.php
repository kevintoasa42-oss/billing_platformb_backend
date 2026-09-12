<?php

namespace App\Contexto\Menu\Infraestructura\Eloquent\Mappers;

use App\Contexto\Menu\Dominio\Mappers\MenuMapperInterface;
use App\Contexto\Menu\Dominio\Modelos\Menu;

class MenuMapper implements MenuMapperInterface
{
    public function toDomain(array $data): Menu
    {
        $hijos = [];
        if (!empty($data['hijos'])) {
            foreach ($data['hijos'] as $hijo) {
                $hijos[] = $this->toDomain(is_array($hijo) ? $hijo : $hijo->toArray());
            }
        }

        return new Menu(
            id: $data['id'] ?? null,
            nombre: $data['nombre'] ?? null,
            ruta: $data['ruta'] ?? null,
            icono: $data['icono'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            orden: $data['orden'] ?? 0,
            hijos: $hijos,
        );
    }

    public function toEloquent(Menu $menu): array
    {
        return [
            'nombre' => $menu->nombre,
            'ruta' => $menu->ruta,
            'icono' => $menu->icono,
            'parent_id' => $menu->parent_id,
            'orden' => $menu->orden,
        ];
    }
}
