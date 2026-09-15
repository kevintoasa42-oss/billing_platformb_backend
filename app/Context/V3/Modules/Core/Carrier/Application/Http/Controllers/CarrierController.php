<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Core\Carrier\Application\Http\Controllers;

use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierAllocationRequest;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierOnboardingRequest;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierPaymentAccountRequest;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierReceivedDocumentRequest;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierSignatureRequest;
use App\Context\V3\Modules\Core\Carrier\Application\Http\Requests\CarrierUpdateRequest;
use App\Context\V3\Modules\Core\Carrier\Application\UseCases\CarrierOnboardingUseCase;
use App\Context\V3\Shared\Infrastructure\Laravel\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CarrierController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CarrierOnboardingUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success(
            $this->useCase->list($this->tenantId($request), $request->query()),
            'Socios transportistas cargados.',
        );
    }

    public function store(CarrierOnboardingRequest $request): JsonResponse
    {
        $key = trim((string) $request->header('Idempotency-Key', ''));

        return $this->success(
            $this->useCase->onboard($this->tenantId($request), $request->validated(), $key, $this->userId($request)),
            'Socio transportista creado o reutilizado.',
            Response::HTTP_CREATED,
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        return $this->success(
            $this->useCase->show($this->tenantId($request), $id),
            'Ficha del socio transportista cargada.',
        );
    }

    public function update(CarrierUpdateRequest $request, string $id): JsonResponse
    {
        return $this->success(
            $this->useCase->update($this->tenantId($request), $id, $request->validated(), $this->userId($request)),
            'Ficha del socio transportista actualizada.',
        );
    }

    public function paymentAccount(CarrierPaymentAccountRequest $request, string $id): JsonResponse
    {
        return $this->success(
            $this->useCase->updatePaymentAccount($this->tenantId($request), $id, $request->validated(), $this->userId($request)),
            'Cuenta de pago actualizada.',
        );
    }

    public function signature(CarrierSignatureRequest $request, string $id): JsonResponse
    {
        return $this->success(
            $this->useCase->updateSignature($this->tenantId($request), $id, $request->validated(), $this->userId($request)),
            'Metadatos de firma actualizados.',
        );
    }

    public function document(CarrierReceivedDocumentRequest $request, string $id): JsonResponse
    {
        return $this->success(
            $this->useCase->recordDocument($this->tenantId($request), $id, $request->validated(), $this->userId($request)),
            'Documento recibido registrado.',
            Response::HTTP_CREATED,
        );
    }

    public function cancelDocument(Request $request, string $id, string $documentId): JsonResponse
    {
        return $this->success(
            $this->useCase->cancelDocument($this->tenantId($request), $id, $documentId, (string) $request->input('reason', ''), $this->userId($request)),
            'Documento recibido cancelado.',
        );
    }

    public function allocate(CarrierAllocationRequest $request, string $id): JsonResponse
    {
        $key = trim((string) $request->header('Idempotency-Key', ''));

        return $this->success(
            $this->useCase->allocateDocument($this->tenantId($request), $id, $request->validated(), $key, $this->userId($request)),
            'Conciliación registrada.',
            Response::HTTP_CREATED,
        );
    }

    public function reverseAllocation(Request $request, string $id, string $allocationId): JsonResponse
    {
        return $this->success(
            $this->useCase->reverseAllocation($this->tenantId($request), $id, $allocationId, (string) $request->input('reason', ''), $this->userId($request)),
            'Conciliación revertida.',
        );
    }

    public function clearReview(Request $request, string $id, string $operationId): JsonResponse
    {
        return $this->success(
            $this->useCase->clearSettlementReview($this->tenantId($request), $id, $operationId, (string) $request->input('reason', ''), $this->userId($request)),
            'Regularización confirmada.',
        );
    }

    public function audit(Request $request, string $id): JsonResponse
    {
        return $this->success(
            $this->useCase->audit($this->tenantId($request), $id),
            'Auditoría cargada.',
        );
    }

    private function tenantId(Request $request): string
    {
        $tenantId = $request->attributes->get('v3.tenant_id');

        if (! is_string($tenantId) || $tenantId === '') {
            throw new DomainException('El contexto tenant es obligatorio.');
        }

        return $tenantId;
    }

    private function userId(Request $request): ?string
    {
        $userId = $request->attributes->get('v3.user_id');

        return is_string($userId) && $userId !== '' ? $userId : null;
    }
}
