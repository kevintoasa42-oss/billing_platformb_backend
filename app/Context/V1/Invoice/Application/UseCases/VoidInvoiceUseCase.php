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
        $invoice = $this->repository->getById($id);

        if (!$invoice) {
            return false;
        }

        // Consumidor final invoices cannot be voided (SRI rule)
        if (($invoice['buyer_identification_type'] ?? null) === '07') {
            throw new \InvalidArgumentException(
                'Consumidor final invoices cannot be voided.'
            );
        }

        return $this->repository->changeStatus($id, 'ANULADO');
    }
}
