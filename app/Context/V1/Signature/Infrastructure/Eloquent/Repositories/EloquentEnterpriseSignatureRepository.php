<?php

namespace App\Context\V1\Signature\Infrastructure\Eloquent\Repositories;

use App\Context\V1\Signature\Domain\Mappers\EnterpriseSignatureMapper;
use App\Context\V1\Signature\Domain\Models\EnterpriseSignature;
use App\Context\V1\Signature\Domain\Repositories\EnterpriseSignatureRepositoryInterface;
use App\Context\V1\Signature\Infrastructure\Eloquent\Mappers\EloquentEnterpriseSignatureMapper;
use App\Models\EnterpriseSignatureModel;

class EloquentEnterpriseSignatureRepository implements EnterpriseSignatureRepositoryInterface
{
    public function getByEnterpriseId(int $enterpriseId): ?array
    {
        $model = EnterpriseSignatureModel::where('enterprise_id', $enterpriseId)->first();

        return $model ? EnterpriseSignatureMapper::toDtoArray(EloquentEnterpriseSignatureMapper::toDomain($model)) : null;
    }

    public function create(EnterpriseSignature $signature): array
    {
        $model = EnterpriseSignatureModel::create(EloquentEnterpriseSignatureMapper::toModel($signature));

        return EnterpriseSignatureMapper::toDtoArray(EloquentEnterpriseSignatureMapper::toDomain($model->fresh()));
    }

    public function update(EnterpriseSignature $signature): array
    {
        $model = EnterpriseSignatureModel::findOrFail($signature->id);
        $model->update(EloquentEnterpriseSignatureMapper::toModel($signature));

        return EnterpriseSignatureMapper::toDtoArray(EloquentEnterpriseSignatureMapper::toDomain($model->fresh()));
    }

    public function changeStatus(int $id, bool $status): bool
    {
        return EnterpriseSignatureModel::where('id', $id)->update(['status' => $status]) > 0;
    }
}
