<?php

namespace App\Context\V1\Modules\Signature\Domain\Mappers;

use App\Context\V1\Modules\Signature\Application\DTOs\EnterpriseSignatureDTO;
use App\Context\V1\Modules\Signature\Domain\Models\EnterpriseSignature;

class EnterpriseSignatureMapper
{
    public static function fromDto(EnterpriseSignatureDTO $dto): EnterpriseSignature
    {
        return new EnterpriseSignature(
            id: $dto->id,
            enterprise_id: $dto->enterprise_id,
            file_name: $dto->file_name,
            file_path: $dto->file_path,
            password: $dto->password,
            expires_at: $dto->expires_at,
            environment: $dto->environment,
            emission_type: $dto->emission_type,
            status: $dto->status,
        );
    }

    public static function toDtoArray(EnterpriseSignature $signature): array
    {
        return [
            'id' => $signature->id,
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
