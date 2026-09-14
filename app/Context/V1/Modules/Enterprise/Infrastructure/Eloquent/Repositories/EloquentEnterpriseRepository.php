<?php

namespace App\Context\V1\Modules\Enterprise\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Modules\Enterprise\Domain\Mappers\EnterpriseMapperInterface;
use App\Context\V1\Modules\Enterprise\Domain\Models\Enterprise;
use App\Context\V1\Modules\Enterprise\Domain\Repositories\EnterpriseRepositoryInterface;
use App\Models\EnterpriseModel;

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
        return EnterpriseModel::orderBy('name')
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
        $user = \App\Models\UserModel::find($userId);

        if (!$user) {
            return [];
        }

        return $user->enterprises()
            ->get()
            ->map(fn ($m) => $this->mapper->toDomain($m->toArray()))
            ->all();
    }
}
