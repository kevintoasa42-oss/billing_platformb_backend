<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Repositories;

use App\Context\Enterprise\Domain\Mappers\RoleMapperInterface;
use App\Context\Enterprise\Domain\Models\Role;
use App\Context\Enterprise\Domain\Repositories\RoleRepositoryInterface;
use App\Context\Enterprise\Infrastructure\Eloquent\Models\RoleModel;
use App\Context\Enterprise\Infrastructure\Eloquent\Models\UserModel;

class EloquentRoleRepository implements RoleRepositoryInterface
{
    public function __construct(
        private RoleMapperInterface $mapper,
    ) {}

    public function crear(Role $rol): Role
    {
        $model = RoleModel::create($this->mapper->toEloquent($rol));

        return $this->mapper->toDomain($model->toArray());
    }

    public function listar(): array
    {
        return RoleModel::orderBy('nombre')
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }

    public function buscarPorId(int $id): ?Role
    {
        $model = RoleModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorNombre(string $nombre): ?Role
    {
        $model = RoleModel::where('nombre', $nombre)->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function listarPorUser(int $userId): array
    {
        $user = UserModel::find($userId);

        if (!$user) {
            return [];
        }

        return $user->roles()
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }
}
