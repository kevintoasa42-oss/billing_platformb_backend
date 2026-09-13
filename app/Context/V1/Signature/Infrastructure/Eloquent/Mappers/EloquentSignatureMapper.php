<?php

namespace App\Context\V1\Signature\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Signature\Domain\Models\Signature;
use App\Models\SignatureModel;

class EloquentSignatureMapper
{
    public static function toDomain(object $model): Signature
    {
        /** @var SignatureModel $model */
        return new Signature(
            id: $model->id,
            carrier_id: $model->carrier_id,
            file_name: $model->file_name,
            file_path: $model->file_path,
            password: $model->password,
            expires_at: $model->expires_at?->format('Y-m-d'),
            environment: $model->environment,
            emission_type: $model->emission_type,
            status: $model->status,
        );
    }

    public static function toModel(Signature $signature): array
    {
        return [
            'carrier_id' => $signature->carrier_id,
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
