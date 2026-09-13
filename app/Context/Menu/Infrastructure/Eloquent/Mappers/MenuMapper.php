<?php

namespace App\Context\Menu\Infrastructure\Eloquent\Mappers;

use App\Context\Menu\Domain\Mappers\MenuMapperInterface;
use App\Context\Menu\Domain\Models\Menu;

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
            name: $data['name'] ?? null,
            route: $data['route'] ?? null,
            icon: $data['icon'] ?? null,
            parent_id: $data['parent_id'] ?? null,
            order: $data['order'] ?? 0,
            hijos: $hijos,
        );
    }

    public function toEloquent(Menu $menu): array
    {
        return [
            'name' => $menu->name,
            'route' => $menu->route,
            'icon' => $menu->icon,
            'parent_id' => $menu->parent_id,
            'order' => $menu->order,
        ];
    }
}
