<?php

namespace App\Context\V1\Invoice\Application\UseCases;

use App\Context\V1\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Invoice\Domain\Mappers\InvoiceMapper;
use App\Context\V1\Invoice\Domain\Repositories\InvoiceRepositoryInterface;

class UpdateInvoiceUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $repository,
    ) {}

    public function execute(InvoiceDTO $dto): InvoiceDTO
    {
        $invoice = InvoiceMapper::fromDto($dto);

        return InvoiceDTO::fromArray($this->repository->update($invoice));
    }
}
