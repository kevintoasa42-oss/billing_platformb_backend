<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Controllers;

use App\Context\V3\Modules\Fiscal\Invoice\Application\DTOs\InvoiceDraftSaveDTO;
use App\Context\V3\Modules\Fiscal\Invoice\Application\Http\Requests\InvoiceDraftSaveRequest;
use App\Context\V3\Modules\Fiscal\Invoice\Application\UseCases\InvoiceDraftUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class InvoiceDraftController
{
    public function __construct(
        private readonly InvoiceDraftUseCase $useCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $drafts = $this->useCase->list($userId);

        return new JsonResponse([
            'data' => array_map(fn ($d) => $d->toArray(), $drafts),
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $draft = $this->useCase->current($userId);
        if ($draft === null) {
            return new JsonResponse(null, 204);
        }

        return new JsonResponse($draft->toArray());
    }

    public function save(InvoiceDraftSaveRequest $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $dto = InvoiceDraftSaveDTO::fromArray($request->validated());
        $draft = $this->useCase->save($userId, $dto);

        return new JsonResponse($draft->toArray(), 200);
    }

    public function delete(Request $request, ?string $publicId = null): Response
    {
        $userId = $this->resolveUserId($request);
        $revision = $request->query('revision') !== null ? (int) $request->query('revision') : null;
        $this->useCase->delete($userId, $publicId, $revision);

        return new Response(status: 204);
    }

    public function options(): Response
    {
        return new Response(status: 204);
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
}
