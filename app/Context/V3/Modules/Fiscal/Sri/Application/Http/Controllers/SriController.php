<?php

declare(strict_types=1);

namespace App\Context\V3\Modules\Fiscal\Sri\Application\Http\Controllers;

use App\Context\V3\Modules\Fiscal\Sri\Application\UseCases\GetDispatchControlUseCase;
use App\Context\V3\Modules\Fiscal\Sri\Application\UseCases\UpdateDispatchControlUseCase;
use App\Context\V3\Modules\Fiscal\Sri\Application\UseCases\UpdateSriModeUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

final class SriController
{
    public function __construct(
        private readonly UpdateSriModeUseCase $updateSriModeUseCase,
        private readonly GetDispatchControlUseCase $getDispatchControlUseCase,
        private readonly UpdateDispatchControlUseCase $updateDispatchControlUseCase,
    ) {}

    public function updateMode(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        if ($tenantId === null) {
            return new JsonResponse(['status' => false, 'message' => 'No autorizado.', 'error' => ['code' => 'unauthorized']], 401);
        }

        $mode = strtolower((string) $request->input('mode', 'mock'));

        try {
            $resolved = $this->updateSriModeUseCase->execute($tenantId, $mode);

            return new JsonResponse([
                'status' => true,
                'message' => 'Modo fiscal actualizado.',
                'data' => ['mode' => $resolved->value],
            ]);
        } catch (RuntimeException $e) {
            return new JsonResponse([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => ['code' => 'fiscal_mode_unavailable'],
            ], 422);
        }
    }

    public function dispatchControl(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        if ($tenantId === null) {
            return new JsonResponse(['status' => false, 'message' => 'No autorizado.', 'error' => ['code' => 'unauthorized']], 401);
        }

        $control = $this->getDispatchControlUseCase->execute($tenantId);

        return new JsonResponse([
            'status' => true,
            'message' => 'Control fiscal cargado.',
            'data' => $control->toArray(),
        ]);
    }

    public function updateDispatchControl(Request $request): JsonResponse
    {
        $tenantId = $this->resolveTenantId($request);
        if ($tenantId === null) {
            return new JsonResponse(['status' => false, 'message' => 'No autorizado.', 'error' => ['code' => 'unauthorized']], 401);
        }

        try {
            $control = $this->updateDispatchControlUseCase->execute($tenantId, $request->all());

            return new JsonResponse([
                'status' => true,
                'message' => 'Control fiscal actualizado.',
                'data' => $control->toArray(),
            ]);
        } catch (RuntimeException $e) {
            return new JsonResponse([
                'status' => false,
                'message' => $e->getMessage(),
                'error' => ['code' => 'fiscal_control_not_applicable'],
            ], 409);
        }
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
