<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Pipelines;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Worker\Domain\Ports\FiscalPipelineInterface;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\InvoiceRideRenderer;

/**
 * Mock/lab fiscal pipeline. Generates synthetic artifacts without touching the SRI.
 */
final class LabFiscalPipeline implements FiscalPipelineInterface
{
    public function __construct(
        private readonly InvoiceRideRenderer $rideRenderer,
    ) {}

    public function process(string $tenantId, string $operation, Invoice $invoice, array $context): array
    {
        return match ($operation) {
            'xml' => $this->generateXml($invoice),
            'signature' => $this->generateSignedXml($invoice),
            'send' => $this->simulateSend(),
            'authorization' => $this->simulateAuthorization($invoice),
            'authorized_xml' => $this->generateAuthorizedXml($invoice),
            'pdf' => $this->generatePdf($invoice),
            'delivery' => $this->deliver($invoice),
            default => [],
        };
    }

    private function generateXml(Invoice $invoice): array
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<laboratorio validezFiscal="ninguna">'."\n"
            .'  <documento numero="'.$invoice->documentNumber.'" total="'.$invoice->total.'"/>'."\n"
            .'</laboratorio>'."\n";

        return [
            'artifact' => [
                'kind' => 'xml',
                'content' => $xml,
                'content_type' => 'application/xml',
            ],
        ];
    }

    private function generateSignedXml(Invoice $invoice): array
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<laboratorio validezFiscal="ninguna" firmado="simulado">'."\n"
            .'  <documento numero="'.$invoice->documentNumber.'" total="'.$invoice->total.'"/>'."\n"
            .'</laboratorio>'."\n";

        return [
            'artifact' => [
                'kind' => 'signed_xml',
                'content' => $xml,
                'content_type' => 'application/xml',
            ],
        ];
    }

    private function simulateSend(): array
    {
        return ['status' => 'received'];
    }

    private function simulateAuthorization(Invoice $invoice): array
    {
        return [
            'status' => 'authorized',
            'authorization_number' => $invoice->documentNumber.'-SIMULADO',
            'authorization_date' => now()->toIso8601String(),
        ];
    }

    private function generateAuthorizedXml(Invoice $invoice): array
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<laboratorio validezFiscal="ninguna" autorizado="simulado">'."\n"
            .'  <documento numero="'.$invoice->documentNumber.'" total="'.$invoice->total.'"/>'."\n"
            .'</laboratorio>'."\n";

        return [
            'artifact' => [
                'kind' => 'authorized_xml',
                'content' => $xml,
                'content_type' => 'application/xml',
            ],
        ];
    }

    private function generatePdf(Invoice $invoice): array
    {
        $pdf = $this->rideRenderer->renderLab($invoice->documentNumber, $invoice->total);

        return [
            'artifact' => [
                'kind' => 'pdf',
                'content' => $pdf,
                'content_type' => 'application/pdf',
            ],
        ];
    }

    private function deliver(Invoice $invoice): array
    {
        return ['status' => 'simulated'];
    }
}
