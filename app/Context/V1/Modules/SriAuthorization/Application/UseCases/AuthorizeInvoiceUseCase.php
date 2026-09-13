<?php

namespace App\Context\V1\Modules\SriAuthorization\Application\UseCases;

use App\Context\V1\Modules\Invoice\Application\DTOs\InvoiceDTO;
use App\Context\V1\Modules\Invoice\Domain\Mappers\InvoiceMapper;
use App\Context\V1\Modules\Invoice\Domain\Repositories\InvoiceRepositoryInterface;
use App\Context\V1\Modules\SriAuthorization\Domain\Models\InvoiceSriLog;
use App\Context\V1\Modules\SriAuthorization\Domain\Repositories\InvoiceSriLogRepositoryInterface;
use App\Context\V1\Modules\SriAuthorization\Domain\Services\SriAuthorizationService;
use App\Context\V1\Modules\SriAuthorization\Domain\Services\SriReceptionService;
use App\Context\V1\Modules\XmlGeneration\Domain\Services\InvoiceXmlBuilder;

class AuthorizeInvoiceUseCase
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
        private InvoiceSriLogRepositoryInterface $sriLogRepository,
        private InvoiceXmlBuilder $xmlBuilder,
        private SriReceptionService $receptionService,
        private SriAuthorizationService $authorizationService,
    ) {}

    /**
     * Full SRI authorization flow:
     * 1. Get invoice from DB
     * 2. Generate XML
     * 3. Send to SRI reception
     * 4. Log reception response
     * 5. If received, wait, then query authorization
     * 6. Log authorization response
     * 7. Update invoice status
     *
     * @return array{status: bool, response: string|array}
     */
    public function execute(int $invoiceId): array
    {
        $data = $this->invoiceRepository->getById($invoiceId);

        if (!$data) {
            return ['status' => false, 'response' => 'Invoice not found.'];
        }

        $invoice = InvoiceMapper::fromDto(InvoiceDTO::fromArray($data));
        $environment = $invoice->environment ?? '1';
        $accessKey = $invoice->access_key;

        // 1. Generate XML
        $xml = $this->xmlBuilder->build($invoice);

        // 2. Send to SRI reception
        $receptionResult = $this->receptionService->send($xml, $environment);

        // 3. Log reception response
        $this->logSriResponse($invoiceId, $accessKey, 'reception', $receptionResult, $environment);

        if (!$receptionResult['status']) {
            // Update invoice status to RECHAZADO
            $this->invoiceRepository->changeStatus($invoiceId, 'RECHAZADO');

            return [
                'status' => false,
                'response' => $receptionResult['message'] ?? 'Reception failed',
            ];
        }

        // 4. Wait before querying authorization
        sleep((int) config('sri.auth_wait_seconds', 3));

        // 5. Query SRI authorization
        $authResult = $this->authorizationService->query($accessKey, $environment);

        // 6. Log authorization response
        $this->logSriResponse($invoiceId, $accessKey, 'authorization', $authResult, $environment, $authResult['authorization_date'] ?? null);

        // 7. Update invoice status
        if ($authResult['status']) {
            $this->invoiceRepository->changeStatus($invoiceId, 'AUTORIZADO');

            return [
                'status' => true,
                'response' => [
                    'estado' => $authResult['sri_state'],
                    'fechaAutorizacion' => $authResult['authorization_date'],
                ],
            ];
        }

        $this->invoiceRepository->changeStatus($invoiceId, 'RECHAZADO');

        return [
            'status' => false,
            'response' => $authResult['message'] ?? 'Authorization failed',
        ];
    }

    private function logSriResponse(
        int $invoiceId,
        string $accessKey,
        string $operationType,
        array $result,
        string $environment,
        ?string $authorizationDate = null,
    ): void {
        $this->sriLogRepository->create(new InvoiceSriLog(
            invoice_header_id: $invoiceId,
            access_key: $accessKey,
            operation_type: $operationType,
            status: $result['status'],
            sri_state: $result['sri_state'] ?? null,
            response_message: $result['message'] ?? null,
            raw_response: $result['raw'] ?? null,
            environment: $environment,
            authorization_date: $authorizationDate,
        ));
    }
}
