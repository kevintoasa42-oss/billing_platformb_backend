<?php

namespace App\Context\V1\Invoice\Application\UseCases;

use App\Context\V1\EmissionPoints\Application\Adapters\InvoiceSequentialResolverInterface;
use App\Context\V1\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Invoice\Domain\Mappers\InvoiceMapper;
use App\Context\V1\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use App\Context\V1\Invoice\Domain\Services\InvoiceCalculationService;

class UpdateInvoiceUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
        private InvoiceCalculationService $calculationService,
        private InvoiceSequentialResolverInterface $sequentialResolver,
    ) {}

    public function execute(InvoiceDTO $dto, int $enterpriseId): InvoiceDTO
    {
        $invoice = InvoiceMapper::fromDto($dto);

        // If branch office / emission point are being changed, re-resolve
        // the SRI codes and generate a unique sequential (excluding self).
        if ($invoice->branch_office_id && $invoice->emission_point_id) {
            $resolved = $this->sequentialResolver->resolve(
                branchOfficeId: $invoice->branch_office_id,
                emissionPointId: $invoice->emission_point_id,
                requestedSequential: $invoice->sequential,
                excludeInvoiceId: $invoice->id,
            );

            $invoice->establishment = $resolved['establishment'];
            $invoice->emission_point = $resolved['emission_point'];
            $invoice->sequential = $resolved['sequential'];
        }

        // Recalculate totals, aggregate taxes, enrich with SRI catalog data,
        // resolve environment/emission_type from signature, generate access key
        $invoice = $this->calculationService->calculateAndEnrich($invoice, $enterpriseId);

        return InvoiceDTO::fromArray($this->repository->update($invoice));
    }
}
