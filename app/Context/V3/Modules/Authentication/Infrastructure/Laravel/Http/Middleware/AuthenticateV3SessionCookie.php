<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Middleware;

use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Resolves the V3 HttpOnly/Bearer session against auth.sessions. */
final class AuthenticateV3SessionCookie
{
    public function __construct(private readonly AuthenticationRepositoryInterface $repository, private readonly Container $container) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken()
            ?: $request->cookie((string) config('auth.v3_session_cookie_name', 'billing_v3_session'));
        if (! is_string($token) || $token === '') {
            return $this->unauthenticated();
        }

        $session = $this->repository->resolveSession(hash('sha256', $token));
        if (! $session) {
            return $this->unauthenticated();
        }

        $request->attributes->set('v3.authentication_session', $session);
        $this->container->scoped(
            AuthenticationSession::class,
            static fn (): AuthenticationSession => $session,
        );
        $request->attributes->set('v3.tenant_id', $session->tenantId);

        return $next($request);
    }

    private function unauthenticated(): Response
    {
        return response()->json([
            'type' => 'https://artra.cloud/problems/unauthenticated',
            'title' => 'La sesión no es válida.',
            'status' => Response::HTTP_UNAUTHORIZED,
            'code' => 'unauthenticated',
            'message' => 'La sesión no es válida.',
        ], Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/problem+json']);
    }
}
