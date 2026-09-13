<?php

namespace App\Context\V1\Signature\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Signature\Domain\Mappers\SignatureMapper;
use App\Context\V1\Signature\Domain\Models\Signature;
use App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface;
use App\Context\V1\Signature\Infrastructure\Eloquent\Mappers\EloquentSignatureMapper;
use App\Models\SignatureModel;

class EloquentSignatureRepository implements SignatureRepositoryInterface
{
    public function getById(int $id): ?array
    {
        $model = SignatureModel::with('carrier')->find($id);

        return $model ? SignatureMapper::toDtoArray(EloquentSignatureMapper::toDomain($model)) : null;
    }

    public function create(Signature $signature): array
    {
        $model = SignatureModel::create(EloquentSignatureMapper::toModel($signature));

        return SignatureMapper::toDtoArray(EloquentSignatureMapper::toDomain($model->fresh()));
    }

    public function update(Signature $signature): array
    {
        $model = SignatureModel::findOrFail($signature->id);
        $model->update(EloquentSignatureMapper::toModel($signature));

        return SignatureMapper::toDtoArray(EloquentSignatureMapper::toDomain($model->fresh()));
    }

    public function changeStatus(int $id, bool $status): bool
    {
        return SignatureModel::where('id', $id)->update(['status' => $status]) > 0;
    }
}
