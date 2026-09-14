<?php

declare(strict_types=1);

namespace App\Context\V3\Shared;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Resuelve el tenant_id y user_id desde la sesión consolidada
 * inyectada por AuthenticateConsolidatedSession.
 *
 * Los endpoints v3/core no deben pedir tenant_id ni id en el body:
 * - tenant_id se toma automáticamente de la sesión.
 * - id se genera con UUID v4 si el cliente no lo envía.
 */
trait ResolvesTenantContext
{
    /**
     * Devuelve el tenant_id de la sesión consolidada activa.
     */
    protected function tenantId(Request $request): string
    {
        $session = $request->attributes->get('consolidated.session');

        $tenantId = (string) ($session['tenant_id'] ?? '');
        if (! Str::isUuid($tenantId)) {
            throw new AccessDeniedHttpException('La sesión no tiene una empresa activa válida.');
        }

        return $tenantId;
    }

    /**
     * Devuelve el user_id de la sesión consolidada activa.
     */
    protected function userId(Request $request): string
    {
        $session = $request->attributes->get('consolidated.session');

        $userId = (string) ($session['user_id'] ?? '');
        if (! Str::isUuid($userId)) {
            throw new AccessDeniedHttpException('La sesión no tiene un usuario válido.');
        }

        return $userId;
    }

    /**
     * Mezcla el tenant_id de la sesión y genera un id UUID si no viene.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withTenant(Request $request, array $data): array
    {
        $data['tenant_id'] = $this->tenantId($request);

        if (! isset($data['id']) || $data['id'] === null || $data['id'] === '') {
            $data['id'] = Str::uuid()->toString();
        }

        return $data;
    }

    /**
     * Devuelve el company_id asociado al tenant de la sesión.
     * En este modelo, company_id coincide con tenant_id.
     */
    protected function companyId(Request $request): string
    {
        return $this->tenantId($request);
    }

    /**
     * Mezcla tenant_id, company_id (si aplica) y genera un id UUID si no viene.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function withTenantAndCompany(Request $request, array $data): array
    {
        $data = $this->withTenant($request, $data);

        if (! isset($data['company_id']) || $data['company_id'] === null || $data['company_id'] === '') {
            $data['company_id'] = $this->companyId($request);
        }

        return $data;
    }
}
