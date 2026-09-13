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

    public function execute(InvoiceDTO $dto, int $enterpriseId): InvoiceDTO
    {
        $invoice = InvoiceMapper::fromDto($dto);

        // Recalculate totals, aggregate taxes, enrich with SRI catalog data,
        // resolve environment/emission_type from signature, generate access key
        $invoice = $this->calculationService->calculateAndEnrich($invoice, $enterpriseId);

        return InvoiceDTO::fromArray($this->repository->update($invoice));
    }
}
