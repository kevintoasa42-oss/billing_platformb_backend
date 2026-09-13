<?php

namespace App\Context\V1\Modules\Signature\Domain\Mappers;

use App\Context\V1\Modules\Signature\Application\DTOs\SignatureDTO;
use App\Context\V1\Modules\Signature\Domain\Models\Signature;

class SignatureMapper
{
    public static function fromDto(SignatureDTO $dto): Signature
    {
        return new Signature(
            id: $dto->id,
            carrier_id: $dto->carrier_id,
            file_name: $dto->file_name,
            file_path: $dto->file_path,
            password: $dto->password,
            expires_at: $dto->expires_at,
            environment: $dto->environment,
            emission_type: $dto->emission_type,
            status: $dto->status,
        );
    }

    public static function toDtoArray(Signature $signature): array
    {
        return [
            'id' => $signature->id,
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

    public static function activate(Signature $signature): void
    {
        $signature->status = true;
    }

    public static function deactivate(Signature $signature): void
    {
        $signature->status = false;
    }
}
