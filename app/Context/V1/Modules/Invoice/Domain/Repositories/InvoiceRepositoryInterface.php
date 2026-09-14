<?php

namespace App\Context\V1\Modules\Invoice\Domain\Repositories;

use App\Context\V1\Modules\Invoice\Domain\Models\InvoiceHeader;

interface InvoiceRepositoryInterface
{
    /**
     * Paginated list of invoices.
     *
     * @return array{data: array, total: int, page: int, perPage: int, lastPage: int}
     */
    public function listPaginated(int $page = 1, int $perPage = 15, ?string $search = null): array;

    public function getById(int $id): ?array;

    public function create(InvoiceHeader $invoice): array;

    public function update(InvoiceHeader $invoice): array;

    public function changeStatus(int $id, string $status): bool;
}
