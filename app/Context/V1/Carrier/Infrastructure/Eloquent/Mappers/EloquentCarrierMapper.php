<?php

namespace App\Context\V1\Carrier\Infrastructure\Eloquent\Mappers;

use App\Context\V1\Carrier\Domain\Models\Carrier;
use App\Models\CarrierModel;

class EloquentCarrierMapper
{
    public static function toDomain(object $model): Carrier
    {
        /** @var CarrierModel $model */
        return new Carrier(
            id: $model->id,
            ruc: $model->ruc,
            placa: $model->placa,
            name: $model->name,
            tradename: $model->tradename,
            matrix_address: $model->matrix_address,
            special_taxpayer: $model->special_taxpayer,
            accounting_required: $model->accounting_required,
            status: $model->status,
        );
    }

    public static function toModel(Carrier $carrier): array
    {
        return [
            'ruc' => $carrier->ruc,
            'placa' => $carrier->placa,
            'name' => $carrier->name,
            'tradename' => $carrier->tradename,
            'matrix_address' => $carrier->matrix_address,
            'special_taxpayer' => $carrier->special_taxpayer,
            'accounting_required' => $carrier->accounting_required,
            'status' => $carrier->status,
        ];
    }
}
