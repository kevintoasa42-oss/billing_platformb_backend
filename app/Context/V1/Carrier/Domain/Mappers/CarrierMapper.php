<?php

namespace App\Context\V1\Carrier\Domain\Mappers;

use App\Context\V1\Carrier\Application\DTOs\CarrierDTO;
use App\Context\V1\Carrier\Domain\Models\Carrier;

class CarrierMapper
{
    public static function fromDto(CarrierDTO $dto): Carrier
    {
        return new Carrier(
            id: $dto->id,
            ruc: $dto->ruc,
            name: $dto->name,
            tradename: $dto->tradename,
            matrix_address: $dto->matrix_address,
            special_taxpayer: $dto->special_taxpayer,
            accounting_required: $dto->accounting_required,
            status: $dto->status,
        );
    }

    public static function toDtoArray(Carrier $carrier): array
    {
        return [
            'id' => $carrier->id,
            'ruc' => $carrier->ruc,
            'name' => $carrier->name,
            'tradename' => $carrier->tradename,
            'matrix_address' => $carrier->matrix_address,
            'special_taxpayer' => $carrier->special_taxpayer,
            'accounting_required' => $carrier->accounting_required,
            'status' => $carrier->status,
        ];
    }

    public static function activate(Carrier $carrier): void
    {
        $carrier->status = true;
    }

    public static function deactivate(Carrier $carrier): void
    {
        $carrier->status = false;
    }
}
