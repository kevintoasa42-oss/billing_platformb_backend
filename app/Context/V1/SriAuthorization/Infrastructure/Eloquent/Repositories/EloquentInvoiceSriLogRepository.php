<?php

namespace App\Context\V1\SriAuthorization\Infrastructure\Eloquent\Repositories;

use App\Context\V1\SriAuthorization\Domain\Models\InvoiceSriLog;
use App\Context\V1\SriAuthorization\Domain\Repositories\InvoiceSriLogRepositoryInterface;
use App\Context\V1\SriAuthorization\Infrastructure\Eloquent\Mappers\EloquentInvoiceSriLogMapper;
use App\Models\InvoiceSriLogModel;

class EloquentInvoiceSriLogRepository implements InvoiceSriLogRepositoryInterface
{
    public function create(InvoiceSriLog $log): array
    {
        $model = InvoiceSriLogModel::create(EloquentInvoiceSriLogMapper::toModel($log));

        return EloquentInvoiceSriLogMapper::toDtoArray(EloquentInvoiceSriLogMapper::toDomain($model->fresh()));
    }

    public function getByInvoiceId(int $invoiceId): array
    {
        $models = InvoiceSriLogModel::where('invoice_header_id', $invoiceId)
            ->orderBy('id', 'desc')
            ->get();

        return $models->map(fn ($m) => EloquentInvoiceSriLogMapper::toDtoArray(EloquentInvoiceSriLogMapper::toDomain($m)))
            ->toArray();
    }
}
