<?php

namespace App\Context\V1\Modules\Invoice\Application\UseCases;

use App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class ListInvoicesUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function execute(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        return $this->repository->listPaginated($page, $perPage, $search);
    }
}
