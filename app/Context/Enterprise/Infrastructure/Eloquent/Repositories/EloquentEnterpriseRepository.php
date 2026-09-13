<?php

namespace App\Context\Enterprise\Infrastructure\Eloquent\Repositories;

use App\Context\Enterprise\Domain\Mappers\EnterpriseMapperInterface;
use App\Context\Enterprise\Domain\Models\Enterprise;
use App\Context\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface;
use App\Context\Enterprise\Infrastructure\Eloquent\Models\EnterpriseModel;

class EloquentEnterpriseRepository implements EnterpriseRepositoryInterface
{
    public function __construct(
        private EnterpriseMapperInterface $mapper,
    ) {}

    public function crear(Enterprise $enterprise): Enterprise
    {
        $model = EnterpriseModel::create($this->mapper->toEloquent($enterprise));

        return $this->mapper->toDomain($model->toArray());
    }

    public function listar(): array
    {
        return EnterpriseModel::orderBy('nombre')
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }

    public function buscarPorId(int $id): ?Enterprise
    {
        $model = EnterpriseModel::find($id);

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function buscarPorRuc(string $ruc): ?Enterprise
    {
        $model = EnterpriseModel::where('ruc', $ruc)->first();

        return $model ? $this->mapper->toDomain($model->toArray()) : null;
    }

    public function listarPorUser(int $userId): array
    {
        $user = \App\Context\Enterprise\Infrastructure\Eloquent\Models\UserModel::find($userId);

        if (!$user) {
            return [];
        }

        return $user->enterprises()
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }
}
