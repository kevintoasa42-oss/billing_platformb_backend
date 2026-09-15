<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Worker\Infrastructure\Laravel\Pipelines;

use App\Context\V3\Modules\Fiscal\Invoice\Domain\Models\Invoice;
use App\Context\V3\Modules\Fiscal\Sri\Application\Adapters\SriEnvironmentResolver;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Models\SriEnvironment;
use App\Context\V3\Modules\Fiscal\Sri\Domain\Ports\SriSoapClientInterface;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\InvoiceRideRenderer;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriCredentialSigningService;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriInvoiceXmlGenerator;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriInvoiceXsdValidator;
use App\Context\V3\Modules\Fiscal\Sri\Infrastructure\Laravel\Services\SriXmlValidator;
use App\Context\V3\Modules\Fiscal\Worker\Domain\Ports\FiscalPipelineInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Real SRI/CELCER fiscal pipeline. Generates real XML, signs, sends SOAP, authorizes, generates RIDE.
 */
final class CelcerFiscalPipeline implements FiscalPipelineInterface
{
    public function __construct(
        private readonly SriInvoiceXmlGenerator $xmlGenerator,
        private readonly SriInvoiceXsdValidator $xsdValidator,
        private readonly SriCredentialSigningService $signingService,
        private readonly SriSoapClientInterface $soapClient,
        private readonly InvoiceRideRenderer $rideRenderer,
    ) {}

    public function process(string $tenantId, string $operation, Invoice $invoice, array $context): array
    {
        $sriEnv = SriEnvironmentResolver::resolve();
        $ambiente = $sriEnv->ambiente();

        return match ($operation) {
            'xml' => $this->generateXml($invoice, $context, $ambiente),
            'signature' => $this->signXml($invoice, $context),
            'send' => $this->send($invoice, $context, $ambiente),
            'authorization' => $this->authorize($invoice, $context, $ambiente),
            'authorized_xml' => $this->storeAuthorizedXml($invoice, $context),
            'pdf' => $this->generatePdf($invoice, $context),
            'delivery' => $this->deliver($invoice),
            default => [],
        };
    }

    private function generateXml(Invoice $invoice, array $context, string $ambiente): array
    {
        $enterprise = $context['enterprise'] ?? [];
        $documentSnapshot = $context['document_snapshot'] ?? [];
        $totalsSnapshot = $context['totals_snapshot'] ?? [];

        $xml = $this->xmlGenerator->generate($invoice, $enterprise, $documentSnapshot, $totalsSnapshot, $ambiente);

        $validation = SriXmlValidator::validate($xml);
        if (! $validation['valid']) {
            throw new RuntimeException('XML validation failed: '.implode(', ', $validation['errors']), 422);
        }

        $this->xsdValidator->validate($xml);

        return [
            'artifact' => [
                'kind' => 'xml',
                'content' => $xml,
                'content_type' => 'application/xml',
            ],
        ];
    }

    private function signXml(Invoice $invoice, array $context): array
    {
        $credential = $context['credential'] ?? null;
        if ($credential === null) {
            throw new RuntimeException('No electronic signature credential provided.', 422);
        }

        $xmlArtifact = $context['xml_content'] ?? null;
        if ($xmlArtifact === null) {
            throw new RuntimeException('No XML content found in context.', 422);
        }

        $signedXml = $this->signingService->sign($xmlArtifact, $credential);

        return [
            'artifact' => [
                'kind' => 'signed_xml',
                'content' => $signedXml,
                'content_type' => 'application/xml',
            ],
        ];
    }

    private function send(Invoice $invoice, array $context, string $ambiente): array
    {
        $signedXml = $context['signed_xml_content'] ?? null;
        if ($signedXml === null) {
            throw new RuntimeException('No signed XML content found in context.', 422);
        }

        $result = $this->soapClient->receptionXml($signedXml, $ambiente);

        if (! $result['status']) {
            throw new RuntimeException('SRI reception failed: '.($result['response'] ?? implode(', ', $result['messages'] ?? [])), 422);
        }

        return ['status' => 'received'];
    }

    private function authorize(Invoice $invoice, array $context, string $ambiente): array
    {
        $accessKey = $context['access_key'] ?? $invoice->documentNumber;
        $maxAttempts = (int) config('services.sri.authorization_max_attempts', 5);
        $waitSeconds = (int) config('services.sri.wait_seconds', 5);

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $result = $this->soapClient->authorize($accessKey, $ambiente);

            if ($result['status']) {
                return [
                    'status' => 'authorized',
                    'authorization_number' => $result['numeroAutorizacion'] ?? null,
                    'authorization_date' => $result['fechaAutorizacion'] ?? null,
                    'authorized_xml' => $result['comprobante'] ?? null,
                ];
            }

            if (($result['estado'] ?? '') === 'NO_AUTORIZADO') {
                throw new RuntimeException('SRI rejected document: '.implode(', ', $result['messages'] ?? []), 422);
            }

            if ($attempt < $maxAttempts) {
                sleep($waitSeconds);
            }
        }

        throw new RuntimeException('SRI authorization timeout after '.$maxAttempts.' attempts.', 422);
    }

    private function storeAuthorizedXml(Invoice $invoice, array $context): array
    {
        $authorizedXml = $context['authorized_xml_content'] ?? null;
        if ($authorizedXml === null) {
            $authorizedXml = $context['signed_xml_content'] ?? '';
        }

        return [
            'artifact' => [
                'kind' => 'authorized_xml',
                'content' => $authorizedXml,
                'content_type' => 'application/xml',
            ],
        ];
    }

    private function generatePdf(Invoice $invoice, array $context): array
    {
        $enterprise = $context['enterprise'] ?? [];
        $pdf = $this->rideRenderer->render($invoice, $enterprise);

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
        return ['status' => 'authorized'];
    }
}
