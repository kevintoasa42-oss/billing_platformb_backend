<?php

namespace App\Context\V1\Modules\Invoice\Application\UseCases;

use App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class ChangeInvoiceStatusUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function execute(int $id, string $status): bool
    {
        return $this->repository->changeStatus($id, $status);
    }
}
