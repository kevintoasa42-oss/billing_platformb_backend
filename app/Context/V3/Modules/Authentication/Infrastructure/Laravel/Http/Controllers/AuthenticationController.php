<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Controllers;

use App\Context\V3\Modules\Authentication\Application\DTOs\CompleteSessionDTO;
use App\Context\V3\Modules\Authentication\Application\UseCases\CompleteAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\CreateLoginChallengeUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\GetAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\LogoutAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\SwitchAuthenticationEnterpriseUseCase;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\CompleteAuthenticationSessionRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\CreateLoginChallengeRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\SwitchAuthenticationEnterpriseRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Resources\AuthenticationSessionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticationController extends Controller
{
    public function __construct(
        private readonly CreateLoginChallengeUseCase           $challenge,
        private readonly CompleteAuthenticationSessionUseCase  $completeSession,
        private readonly GetAuthenticationSessionUseCase       $currentSession,
        private readonly SwitchAuthenticationEnterpriseUseCase $switchEnterprise,
        private readonly LogoutAuthenticationSessionUseCase    $logout,
    )
    {
    }

    /**
     * @throws JsonException
     */
    public function challenge(CreateLoginChallengeRequest $request): JsonResponse
    {
        $challenge = $this->challenge->execute($request->credentials());
        $data = [...$challenge->toArray(), 'requires_enterprise' => count($challenge->enterprises) > 1];

        if (count($challenge->enterprises) === 1) {
            $session = $this->completeSession->execute(CompleteSessionDTO::fromChallenge(
                $challenge->cookiePayload(),
                $challenge->enterprises[0]->id,
            ), $this->sessionTtlMinutes());
            $data = [...(new AuthenticationSessionResource($session))->resolve($request), 'requires_enterprise' => false, 'session_ready' => true];

            return $this->success($data, 'Sesión creada.')->withCookie($this->sessionCookie($session->token));
        }

        $data['session_ready'] = false;

        return $this->success($data, 'Credenciales verificadas.')
            ->withCookie($this->challengeCookie($challenge->cookiePayload()));
    }

    public function session(CompleteAuthenticationSessionRequest $request): JsonResponse
    {
        $session = $this->completeSession->execute(CompleteSessionDTO::fromChallenge(
            $this->readChallenge($request),
            (string)$request->validated('enterprise_id'),
        ), $this->sessionTtlMinutes());

        return $this->success(
            [...(new AuthenticationSessionResource($session))->resolve($request), 'session_ready' => true],
            'Sesión creada.',
        )
            ->withCookie($this->sessionCookie($session->token))
            ->withCookie($this->forgetCookie((string)config('auth.v3_challenge_cookie_name', 'billing_v3_challenge')));
    }

    public function me(Request $request): JsonResponse
    {
        $session = $this->currentSession->execute($this->resolvedSession($request));
        return $this->success((new AuthenticationSessionResource($session))->resolve($request), 'Sesión activa.');
    }

    public function switchEnterprise(SwitchAuthenticationEnterpriseRequest $request): JsonResponse
    {
        $session = $this->switchEnterprise->execute(
            $this->resolvedSession($request),
            $request->switchEnterprise(),
            $this->sessionTtlMinutes(),
        );

        return $this->success((new AuthenticationSessionResource($session))->resolve($request), 'Empresa activa cambiada.')
            ->withCookie($this->sessionCookie($session->token));
    }

    public function destroySession(Request $request): JsonResponse
    {
        $this->logout->execute($this->resolvedSession($request)->tokenHash);

        return $this->success(null, 'Sesión cerrada.')
            ->withCookie($this->forgetCookie((string)config('auth.v3_session_cookie_name', 'billing_v3_session')));
    }

    private function resolvedSession(Request $request): AuthenticationSession
    {
        $session = $request->attributes->get('v3.authentication_session');
        if (!$session instanceof AuthenticationSession) {
            throw new AuthenticationException('La sesión no es válida.', 'unauthenticated', 401);
        }

        return $session;
    }

    /** @return array<string, mixed> */
    private function readChallenge(Request $request): array
    {
        $encrypted = $request->cookie((string)config('auth.v3_challenge_cookie_name', 'billing_v3_challenge'));
        if (!is_string($encrypted) || $encrypted === '') {
            throw new AuthenticationException('El desafío de autenticación expiró.', 'authentication_failed', 401);
        }

        $challenge = json_decode(Crypt::decryptString($encrypted), true);
        if (!is_array($challenge)) {
            throw new AuthenticationException('El desafío de autenticación no es válido.', 'authentication_failed', 401);
        }

        return $challenge;
    }

    /** @param array<string, mixed>|null $data */
    private function success(?array $data, string $message): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message, 'data' => $data], Response::HTTP_OK);
    }

    /**
     * @param array{user_id: int, enterprise_ids: int[], expires_at: int} $payload
     * @throws JsonException
     */
    private function challengeCookie(array $payload): Cookie
    {
        return cookie()->make(
            (string)config('auth.v3_challenge_cookie_name', 'billing_v3_challenge'),
            Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
            5,
            (string)config('auth.v3_cookie_path', '/'),
            config('auth.v3_cookie_domain'),
            (bool)config('auth.v3_cookie_secure', false),
            true,
            false,
            (string)config('auth.v3_cookie_same_site', 'lax'),
        );
    }

    private function sessionCookie(string $token): Cookie
    {
        return cookie()->make(
            (string)config('auth.v3_session_cookie_name', 'billing_v3_session'),
            $token,
            (int)config('auth.v3_session_ttl', 120),
            (string)config('auth.v3_cookie_path', '/'),
            config('auth.v3_cookie_domain'),
            (bool)config('auth.v3_cookie_secure', false),
            true,
            false,
            (string)config('auth.v3_cookie_same_site', 'lax'),
        );
    }

    private function forgetCookie(string $name): Cookie
    {
        return cookie()->forget($name, (string)config('auth.v3_cookie_path', '/'), config('auth.v3_cookie_domain'));
    }

    private function sessionTtlMinutes(): int
    {
        return max(5, min(24 * 60, (int)config('auth.v3_session_ttl', 120)));
    }
}
