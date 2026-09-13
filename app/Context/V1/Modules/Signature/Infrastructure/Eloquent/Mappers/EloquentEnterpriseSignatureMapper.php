<?php

namespace App\Context\V1\Modules\Signature\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Modules\Signature\Domain\Models\EnterpriseSignature;
use App\Models\EnterpriseSignatureModel;

class EloquentEnterpriseSignatureMapper
{
    public static function toDomain(object $model): EnterpriseSignature
    {
        /** @var EnterpriseSignatureModel $model */
        return new EnterpriseSignature(
            id: $model->id,
            enterprise_id: $model->enterprise_id,
            file_name: $model->file_name,
            file_path: $model->file_path,
            password: $model->password,
            expires_at: $model->expires_at?->format('Y-m-d'),
            environment: $model->environment,
            emission_type: $model->emission_type,
            status: $model->status,
        );
    }

    public static function toModel(EnterpriseSignature $signature): array
    {
        return [
            'enterprise_id' => $signature->enterprise_id,
            'file_name' => $signature->file_name,
            'file_path' => $signature->file_path,
            'password' => $signature->password,
            'expires_at' => $signature->expires_at,
            'environment' => $signature->environment,
            'emission_type' => $signature->emission_type,
            'status' => $signature->status,
        ];
    }
}
