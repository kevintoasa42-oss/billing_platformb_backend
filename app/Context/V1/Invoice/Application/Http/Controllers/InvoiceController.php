<?php

namespace App\Context\V1\Invoice\Application\Http\Controllers;

use App\Context\V1\Invoice\Application\Http\Requests\ChangeInvoiceStatusRequest;
use App\Context\V1\Invoice\Application\Http\Requests\CreateInvoiceRequest;
use App\Context\V1\Invoice\Application\Http\Requests\UpdateInvoiceRequest;
use App\Context\V1\Invoice\Application\UseCases\ChangeInvoiceStatusUseCase;
use App\Context\V1\Invoice\Application\UseCases\CreateInvoiceUseCase;
use App\Context\V1\Invoice\Application\UseCases\GetInvoiceByIdUseCase;
use App\Context\V1\Invoice\Application\UseCases\ListInvoicesUseCase;
use App\Context\V1\Invoice\Application\UseCases\UpdateInvoiceUseCase;
use App\Context\V1\Invoice\Application\UseCases\VoidInvoiceUseCase;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponse;

    public function __construct(
        private CreateInvoiceUseCase $createUseCase,
        private ListInvoicesUseCase $listUseCase,
        private GetInvoiceByIdUseCase $getByIdUseCase,
        private UpdateInvoiceUseCase $updateUseCase,
        private ChangeInvoiceStatusUseCase $changeStatusUseCase,
        private VoidInvoiceUseCase $voidUseCase,
    ) {}

    /**
     * GET /api/invoices?page=1&perPage=15&search=...
     * Paginated list of invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $perPage = (int) $request->query('perPage', 15);
        $search = $request->query('search');

        $result = $this->listUseCase->execute($page, $perPage, $search);

        return $this->successResponse($result);
    }

    /**
     * GET /api/invoices/{id}
     * Get an invoice by ID (with items, taxes, payments, additional info).
     */
    public function show(int $id): JsonResponse
    {
        $invoice = $this->getByIdUseCase->execute($id);

        if (!$invoice) {
            return $this->errorResponse('Invoice not found.', 404);
        }

        return $this->successResponse($invoice);
    }

    /**
     * POST /api/invoices
     * Create a new invoice with items, taxes, payments and additional info.
     */
    public function store(CreateInvoiceRequest $request): JsonResponse
    {
        $dto = CreateInvoiceRequest::toDTO($request->validated());
        $enterpriseId = $request->user()->currentAccessToken()->getAttribute('enterprise_id');

        try {
            return $this->successResponse($this->createUseCase->execute($dto, $enterpriseId), 201);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * PUT/PATCH /api/invoices/{id}
     * Update an invoice (replaces all children: items, taxes, payments, additional info).
     */
    public function update(int $id, UpdateInvoiceRequest $request): JsonResponse
    {
        $data = array_merge($request->validated(), ['id' => $id]);
        $dto = UpdateInvoiceRequest::toDTO($data);
        $enterpriseId = $request->user()->currentAccessToken()->getAttribute('enterprise_id');

        try {
            return $this->successResponse($this->updateUseCase->execute($dto, $enterpriseId));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Invoice not found.', 404);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }
    }

    /**
     * PATCH /api/invoices/{id}/status
     * Change the status of an invoice (PENDIENTE, RECHAZADO, AUTORIZADO).
     */
    public function changeStatus(int $id, ChangeInvoiceStatusRequest $request): JsonResponse
    {
        $status = $request->validated()['status'];

        $result = $this->changeStatusUseCase->execute($id, $status);

        if (!$result) {
            return $this->errorResponse('Invoice not found.', 404);
        }

        return $this->successResponse('Status updated successfully.');
    }

    /**
     * PATCH /api/invoices/{id}/void
     * Void an invoice (sets status to ANULADO).
     */
    public function void(int $id): JsonResponse
    {
        try {
            $result = $this->voidUseCase->execute($id);
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        if (!$result) {
            return $this->errorResponse('Invoice not found.', 404);
        }

        return $this->successResponse('Invoice voided successfully.');
    }
}
