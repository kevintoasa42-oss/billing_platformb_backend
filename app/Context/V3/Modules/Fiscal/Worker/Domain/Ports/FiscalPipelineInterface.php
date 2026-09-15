<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Domain\Ports;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;

/**
 * Contract for processing a fiscal pipeline operation.
 * Implemented by LabFiscalPipeline (mock) and CelcerFiscalPipeline (real).
 */
interface FiscalPipelineInterface
{
    /**
     * Process a single pipeline operation for a document.
     *
     * @param  string  $tenantId  The tenant identifier.
     * @param  string  $operation  One of: xml, signature, send, authorization, authorized_xml, pdf, delivery.
     * @param  Invoice  $invoice  The domain invoice model.
     * @param  array<string, mixed>  $context  Additional context (enterprise, credential, etc.).
     * @return array{artifact?: array{kind: string, content: string, content_type: string}, status?: string, events?: array}
     */
    public function process(string $tenantId, string $operation, Invoice $invoice, array $context): array;
}
