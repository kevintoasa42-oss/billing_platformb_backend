<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\Http\Controllers;

use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\CurrentAuthenticationSession;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\DTOs\InvoiceDraftCreateDTO;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\Http\Requests\CreateInvoiceDraftRequest;
use App\Context\V3\Modules\Fiscal\InvoiceDraft\Application\UseCases\CreateInvoiceDraftUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class InvoiceDraftController extends Controller
{
    public function __construct(
        private readonly CreateInvoiceDraftUseCase $useCase,
        private readonly CurrentAuthenticationSession $authenticatedSession,
    ) {}

    public function store(CreateInvoiceDraftRequest $request): JsonResponse
    {
        $data = $this->useCase->create(
            $this->authenticatedSession->get()->userId,
            InvoiceDraftCreateDTO::fromArray($request->validated()),
        )->toArray();

        return response()->json([
            'status' => true,
            'message' => 'Borrador creado.',
            'data' => ['draft' => $data],
        ], Response::HTTP_CREATED);
    }
}
