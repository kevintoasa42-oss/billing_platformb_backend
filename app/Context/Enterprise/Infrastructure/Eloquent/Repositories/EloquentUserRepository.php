<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Repositories;

use App\Context\Enterprise\Domain\Mappers\UserMapperInterface;
use App\Context\Enterprise\Domain\Models\User;
use App\Context\Enterprise\Domain\Repositories\UserRepositoryInterface;
use App\Context\Enterprise\Infrastructure\Eloquent\Models\UserModel;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private UserMapperInterface $mapper,
    ) {}

    public function crear(User $user): User
    {
        $model = UserModel::create($this->mapper->toEloquent($user));

        return $this->mapper->toDomain($model->toArray());
    }

    public function buscarPorEmail(string $email): ?User
    {
        $model = UserModel::where('email', $email)->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorId(int $id): ?User
    {
        $model = UserModel::with(['roles', 'enterprises'])->find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function asignarRol(int $userId, int $rolId): void
    {
        $model = UserModel::find($userId);
        if ($model) {
            $model->roles()->syncWithoutDetaching([$rolId]);
        }
    }

    public function asignarEnterprise(int $userId, int $enterpriseId): void
    {
        $model = UserModel::find($userId);
        if ($model) {
            $model->enterprises()->syncWithoutDetaching([$enterpriseId]);
        }
    }
}
