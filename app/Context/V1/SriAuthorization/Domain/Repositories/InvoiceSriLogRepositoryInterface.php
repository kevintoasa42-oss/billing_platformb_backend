<?php

namespace App\Context\V1\SriAuthorization\Domain\Repositories;

use App\Context\V1\SriAuthorization\Domain\Models\InvoiceSriLog;

interface InvoiceSriLogRepositoryInterface
{
    public function create(InvoiceSriLog $log): array;

    public function getByInvoiceId(int $invoiceId): array;
}
