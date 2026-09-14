<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Http\Middleware;

use App\Context\V3\Shared\Tenant\Application\Adapters\TenantContextServiceInterface;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/** Captures the tenant already resolved from the authenticated V3 session. */
final class CaptureTenantContext
{
    public function __construct(private readonly TenantContextServiceInterface $tenantContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->attributes->get('v3.tenant_id');
        if (! is_string($tenantId) || $tenantId === '') {
            return $this->unauthenticated();
        }

        try {
            $context = $this->tenantContext->activate($tenantId);
            $request->attributes->set('v3.tenant_context', $context);

            return $next($request);
        } catch (InvalidArgumentException) {
            return $this->unauthenticated();
        } finally {
            $this->tenantContext->clear();
        }
    }

    private function unauthenticated(): Response
    {
        return response()->json([
            'status' => false,
            'response' => 'La sesión no contiene un tenant V3 válido.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
