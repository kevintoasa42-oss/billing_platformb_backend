<?php

namespace App\Context\V1\Invoice\Application\UseCases;

use App\Context\V1\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Invoice\Domain\Mappers\InvoiceMapper;
use App\Context\V1\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use App\Context\V1\Invoice\Domain\Services\InvoiceCalculationService;

class UpdateInvoiceUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private InvoiceCalculationService $calculationService,
    ) {}

    public function execute(InvoiceDTO $dto): InvoiceDTO
    {
        $invoice = InvoiceMapper::fromDto($dto);

        // Recalculate totals, aggregate taxes, enrich with SRI catalog data
        $invoice = $this->calculationService->calculateAndEnrich($invoice);

        return InvoiceDTO::fromArray($this->repository->update($invoice));
    }
}
