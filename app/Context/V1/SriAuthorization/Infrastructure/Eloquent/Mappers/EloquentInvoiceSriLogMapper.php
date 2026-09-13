<?php

namespace App\Context\V1\SriAuthorization\Infrastructure\Eloquent\Mappers;

use App\Context\V1\SriAuthorization\Domain\Models\InvoiceSriLog;
use App\Models\InvoiceSriLogModel;

class EloquentInvoiceSriLogMapper
{
    public static function toDomain(InvoiceSriLogModel $model): InvoiceSriLog
    {
        return new InvoiceSriLog(
            id: $model->id,
            invoice_header_id: $model->invoice_header_id,
            access_key: $model->access_key,
            operation_type: $model->operation_type,
            status: $model->status,
            sri_state: $model->sri_state,
            response_message: $model->response_message,
            raw_response: $model->raw_response,
            environment: $model->environment,
            authorization_date: $model->authorization_date?->format('Y-m-d H:i:s'),
        );
    }

    public static function toModel(InvoiceSriLog $log): array
    {
        return [
            'invoice_header_id' => $log->invoice_header_id,
            'access_key' => $log->access_key,
            'operation_type' => $log->operation_type,
            'status' => $log->status,
            'sri_state' => $log->sri_state,
            'response_message' => $log->response_message,
            'raw_response' => $log->raw_response,
            'environment' => $log->environment,
            'authorization_date' => $log->authorization_date,
        ];
    }

    public static function toDtoArray(InvoiceSriLog $log): array
    {
        return [
            'id' => $log->id,
            'invoice_header_id' => $log->invoice_header_id,
            'access_key' => $log->access_key,
            'operation_type' => $log->operation_type,
            'status' => $log->status,
            'sri_state' => $log->sri_state,
            'response_message' => $log->response_message,
            'raw_response' => $log->raw_response,
            'environment' => $log->environment,
            'authorization_date' => $log->authorization_date,
        ];
    }
}
