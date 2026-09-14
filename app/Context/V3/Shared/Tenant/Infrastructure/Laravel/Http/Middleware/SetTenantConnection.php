<?php

namespace App\Context\V3\Shared\Tenant\Infrastructure\Laravel\Http\Middleware;

use App\Context\V3\Shared\Tenant\Application\Adapters\TenantConnectionServiceInterface;
use App\Context\V3\Shared\Tenant\Domain\Exceptions\TenantDatabaseNotFoundException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Resolves the tenant from the Sanctum token's enterprise scope. */
final class SetTenantConnection
{
    public function __construct(private readonly TenantConnectionServiceInterface $tenantConnection) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();
        $enterpriseId = $token?->enterprise_id;
        if (! $enterpriseId) {
            return $this->forbidden('Token is not scoped to an enterprise.');
        }

        try {
            $tenant = $this->tenantConnection->connectForEnterprise((int) $enterpriseId);
            $request->attributes->set('tenant_database', $tenant->databaseName);
        } catch (TenantDatabaseNotFoundException) {
            return $this->forbidden('Enterprise not found or tenant database not provisioned.');
        }

        return $next($request);
    }

    private function forbidden(string $message): Response
    {
        return response()->json([
            'status' => false,
            'response' => $message,
        ], Response::HTTP_FORBIDDEN);
    }
}
