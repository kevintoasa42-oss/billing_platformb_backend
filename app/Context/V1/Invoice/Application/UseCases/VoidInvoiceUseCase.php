<?php

namespace App\Context\V1\Invoice\Application\UseCases;

use App\Context\V1\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class VoidInvoiceUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->changeStatus($id, 'ANULADO');
    }
}
