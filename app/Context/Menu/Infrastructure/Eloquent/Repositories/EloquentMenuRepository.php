<?php

namespace App\Context\Menu\Infrastructure\Eloquent\Repositories;

use App\Context\Menu\Domain\Mappers\MenuMapperInterface;
use App\Context\Menu\Domain\Models\Menu;
use App\Context\Menu\Domain\Repositories\MenuRepositoryInterface;
use App\Context\Menu\Infrastructure\Eloquent\Models\MenuModel;

class EloquentMenuRepository implements MenuRepositoryInterface
{
    public function __construct(
        private MenuMapperInterface $mapper,
    ) {}

    public function crear(Menu $menu): Menu
    {
        $model = MenuModel::create($this->mapper->toEloquent($menu));

        return $this->mapper->toDomain($model->toArray());
    }

    public function listar(): array
    {
        $menus = MenuModel::whereNull('parent_id')
            ->with('hijos')
            ->orderBy('order')
            ->get();

        return $menus->map(fn ($m) => $this->mapper->toDomain($m->toArray()))->all();
    }

    public function buscarPorId(int $id): ?Menu
    {
        $model = MenuModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function asignarARol(int $menuId, int $rolId): void
    {
        $model = MenuModel::find($menuId);
        if ($model) {
            $model->roles()->syncWithoutDetaching([$rolId]);
        }
    }

    public function obtenerPorRol(int $rolId): array
    {
        $menus = MenuModel::whereHas('roles', function ($query) use ($rolId) {
            $query->where('roles.id', $rolId);
        })
            ->whereNull('parent_id')
            ->with(['hijos' => function ($query) use ($rolId) {
                $query->whereHas('roles', function ($q) use ($rolId) {
                    $q->where('roles.id', $rolId);
                })->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return $menus->map(fn ($m) => $this->mapper->toDomain($m->toArray()))->all();
    }
}
