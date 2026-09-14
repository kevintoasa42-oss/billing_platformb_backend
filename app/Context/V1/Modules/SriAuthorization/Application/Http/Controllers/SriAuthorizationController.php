<?php

namespace App\Context\V1\Modules\SriAuthorization\Application\Http\Controllers;

use App\Context\V1\Modules\SriAuthorization\Application\UseCases\AuthorizeInvoiceUseCase;
use App\Context\V1\Modules\SriAuthorization\Domain\Repositories\InvoiceSriLogRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SriAuthorizationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AuthorizeInvoiceUseCase $authorizeUseCase,
        private InvoiceSriLogRepositoryInterface $sriLogRepository,
    ) {}

    /**
     * POST /api/invoices/{id}/authorize
     * Sends the invoice XML to the SRI and queries authorization.
     */
    public function authorize(int $id): JsonResponse
    {
        $result = $this->authorizeUseCase->execute($id);

        if (!$result['status']) {
            return $this->errorResponse($result['response'], 422);
        }

        return $this->successResponse($result['response']);
    }

    /**
     * GET /api/invoices/{id}/sri-logs
     * Returns all SRI responses logged for the given invoice.
     */
    public function logs(int $id): JsonResponse
    {
        $logs = $this->sriLogRepository->getByInvoiceId($id);

        return $this->successResponse($logs);
    }
}
