<?php

namespace App\Context\V1\Enterprise\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Enterprise\Domain\Mappers\RoleMapperInterface;
use App\Context\V1\Enterprise\Domain\Models\Role;
use App\Context\V1\Enterprise\Domain\Repositories\RoleRepositoryInterface;
use App\Models\RoleModel;
use App\Models\UserModel;

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
        return RoleModel::orderBy('name')
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }

    public function buscarPorId(int $id): ?Role
    {
        $model = RoleModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorNombre(string $name): ?Role
    {
        $model = RoleModel::where('name', $name)->first();

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
