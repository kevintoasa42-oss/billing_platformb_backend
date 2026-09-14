<?php

namespace App\Context\V1\Modules\XmlGeneration\Application\UseCases;

use App\Context\V1\Modules\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Modules\Invoice\Domain\Mappers\InvoiceMapper;
use App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use App\Context\V1\Modules\XmlGeneration\Domain\Services\InvoiceXmlBuilder;

class GenerateInvoiceXmlUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private InvoiceXmlBuilder $xmlBuilder,
    ) {}

    public function execute(int $invoiceId): ?string
    {
        $data = $this->invoiceRepository->getById($invoiceId);

        if (!$data) {
            return null;
        }

        $invoice = InvoiceMapper::fromDto(InvoiceDTO::fromArray($data));

        return $this->xmlBuilder->build($invoice);
    }
}
