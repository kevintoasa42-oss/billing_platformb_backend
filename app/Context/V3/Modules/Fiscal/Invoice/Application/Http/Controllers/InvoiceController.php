<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Controllers;

use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceCreateDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceQueryDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceVoidDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Requests\InvoiceCreateRequest;
use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Requests\InvoiceVoidRequest;
use App\Context\V3\Modules\Fiscal\Invoice\Application\UseCases\InvoiceUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoiceController
{
    public function __construct(
        private readonly InvoiceUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = InvoiceQueryDTO::fromArray($request->all());
        $result = $this->useCase->list($query);

        return new JsonResponse([
            'data' => array_map(fn ($i) => $i->toArray(), $result['items']),
            'meta' => $result['meta'],
        ]);
    }

    public function store(InvoiceCreateRequest $request): JsonResponse
    {
        $dto = InvoiceCreateDTO::fromArray($request->validated());
        $actorId = $this->resolveUserId($request);
        $invoice = $this->useCase->create($dto, $actorId);

        return new JsonResponse($invoice->toArray(), 201);
    }

    public function summary(): JsonResponse
    {
        return new JsonResponse($this->useCase->summary());
    }

    public function export(): StreamedResponse
    {
        $csv = $this->useCase->export();

        return new StreamedResponse(function () use ($csv): void {
            echo $csv;
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices.csv"',
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $invoice = $this->useCase->show($id);
        if ($invoice === null) {
            return new JsonResponse(['code' => 'invoice_not_found'], 404);
        }

        return new JsonResponse($invoice->toArray());
    }

    public function void(int $id, InvoiceVoidRequest $request): JsonResponse
    {
        $dto = new InvoiceVoidDTO(
            legacyId: $id,
            reasonCode: (string) $request->input('reason_code', 'manual'),
            reasonNote: (string) $request->input('reason_note', ''),
        );
        $invoice = $this->useCase->void($dto);
        if ($invoice === null) {
            return new JsonResponse(['code' => 'invoice_not_found'], 404);
        }

        return new JsonResponse(['deleted' => true]);
    }

    public function sriStatus(int $id): JsonResponse
    {
        $invoice = $this->useCase->show($id);
        if ($invoice === null) {
            return new JsonResponse(['code' => 'invoice_not_found'], 404);
        }

        return new JsonResponse([
            'sri_status' => $invoice->sriStatus,
            'fiscal_status' => $invoice->fiscalStatus,
            'authorization_number' => $invoice->authorizationNumber,
        ]);
    }

    public function cancellationWorkflow(int $id): JsonResponse
    {
        $invoice = $this->useCase->show($id);
        if ($invoice === null) {
            return new JsonResponse(['code' => 'invoice_not_found'], 404);
        }

        return new JsonResponse([
            'capabilities' => $invoice->cancellationCapabilities,
            'reasons' => [],
        ]);
    }

    public function verifyCancellationWorkflow(int $id): JsonResponse
    {
        $invoice = $this->useCase->show($id);
        if ($invoice === null) {
            return new JsonResponse(['code' => 'invoice_not_found'], 404);
        }

        return new JsonResponse(['verified' => true]);
    }

    public function authorize(int $id): JsonResponse
    {
        $invoice = $this->useCase->authorize($id);
        if ($invoice === null) {
            return new JsonResponse(['code' => 'invoice_not_found'], 404);
        }

        return new JsonResponse($invoice->toArray());
    }

    public function artifact(int $id, string $kind): Response|JsonResponse
    {
        $content = $this->useCase->artifact($id, $kind);
        if ($content === null) {
            return new JsonResponse(['code' => 'artifact_not_found'], 404);
        }

        $mime = match ($kind) {
            'xml', 'signed_xml' => 'application/xml',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };

        return new Response($content, 200, ['Content-Type' => $mime]);
    }

    public function xmlArtifact(int $id): Response|JsonResponse
    {
        return $this->artifact($id, 'xml');
    }

    public function signedXmlArtifact(int $id): Response|JsonResponse
    {
        return $this->artifact($id, 'signed_xml');
    }

    public function rideArtifact(int $id): Response|JsonResponse
    {
        return $this->artifact($id, 'pdf');
    }

    public function paymentMethods(): JsonResponse
    {
        return new JsonResponse([
            ['code' => '01', 'name' => 'Sin utilización del sistema financiero'],
            ['code' => '19', 'name' => 'Tarjeta de crédito'],
        ]);
    }

    public function readiness(Request $request): JsonResponse
    {
        $branchId = (int) $request->query('branch_id', 0);
        $issuancePointId = (int) $request->query('issuance_point_id', 0);
        $readiness = $this->useCase->readiness($branchId, $issuancePointId);

        return new JsonResponse($readiness->toArray());
    }

    public function editorContext(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        if ($tenantId === null) {
            return new JsonResponse(['status' => false, 'message' => 'No autorizado.', 'error' => ['code' => 'unauthorized']], 401);
        }

        $context = $this->useCase->editorContext($tenantId);

        return new JsonResponse([
            'status' => true,
            'message' => 'Contexto consolidado cargado.',
            'data' => $context,
        ]);
    }

    private function resolveUserId(Request $request): string
    {
        $session = $request->attributes->get('v3.authentication_session');
        if ($session !== null && method_exists($session, 'getUserId')) {
            return (string) $session->getUserId();
        }
        if (is_object($session) && isset($session->userId)) {
            return (string) $session->userId;
        }

        return '';
    }

    private function resolveTenantId(Request $request): ?string
    {
        $session = $request->attributes->get('v3.authentication_session');
        if ($session === null) {
            return null;
        }

        if (method_exists($session, 'getTenantId')) {
            return (string) $session->getTenantId();
        }

        if (isset($session->tenantId)) {
            return (string) $session->tenantId;
        }

        return null;
    }
}
