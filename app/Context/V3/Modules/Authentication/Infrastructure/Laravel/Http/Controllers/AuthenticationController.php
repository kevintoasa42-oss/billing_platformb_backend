<?php

namespace App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Controllers;

use App\Context\V3\Modules\Authentication\Application\DTOs\CompleteSessionDTO;
use App\Context\V3\Modules\Authentication\Application\UseCases\CompleteAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\CreateLoginChallengeUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\GetAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\LogoutAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\ManageAuthenticationSecurityUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\RefreshAuthenticationSessionUseCase;
use App\Context\V3\Modules\Authentication\Application\UseCases\SwitchAuthenticationEnterpriseUseCase;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\CurrentAuthenticationSession;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\BeginAuthenticationMfaRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\ChangeAuthenticationPasswordRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\CompleteAuthenticationSessionRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\ConfirmAuthenticationMfaRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\CreateLoginChallengeRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\SwitchAuthenticationEnterpriseRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\UpdateAuthenticationPreferencesRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Requests\VerifyAuthenticationPasswordRequest;
use App\Context\V3\Modules\Authentication\Infrastructure\Laravel\Http\Resources\AuthenticationSessionResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use JsonException;
use Symfony\Component\HttpFoundation\Cookie;

final class AuthenticationController extends Controller
{
    public function __construct(
        private readonly CreateLoginChallengeUseCase $challenge,
        private readonly CompleteAuthenticationSessionUseCase $completeSession,
        private readonly GetAuthenticationSessionUseCase $currentSession,
        private readonly SwitchAuthenticationEnterpriseUseCase $switchEnterprise,
        private readonly LogoutAuthenticationSessionUseCase $logout,
        private readonly RefreshAuthenticationSessionUseCase $refreshSession,
        private readonly ManageAuthenticationSecurityUseCase $security,
        private readonly CurrentAuthenticationSession $authenticatedSession,
    ) {}

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

            return response()->json([
                'status' => true,
                'message' => 'Sesión creada.',
                'data' => $data,
            ])->withCookie($this->sessionCookie($session->token));
        }

        $data['session_ready'] = false;

        return response()->json([
            'status' => true,
            'message' => 'Credenciales verificadas.',
            'data' => $data,
        ])->withCookie($this->challengeCookie($challenge->cookiePayload()));
    }

    public function session(CompleteAuthenticationSessionRequest $request): JsonResponse
    {
        $session = $this->completeSession->execute(CompleteSessionDTO::fromChallenge(
            $this->readChallenge($request),
            (string) $request->validated('enterprise_id'),
        ), $this->sessionTtlMinutes());

        return response()->json([
            'status' => true,
            'message' => 'Sesión creada.',
            'data' => [...(new AuthenticationSessionResource($session))->resolve($request), 'session_ready' => true],
        ])
            ->withCookie($this->sessionCookie($session->token))
            ->withCookie($this->forgetCookie((string) config('auth.v3_challenge_cookie_name', 'billing_v3_challenge')));
    }

    public function me(Request $request): JsonResponse
    {
        $session = $this->currentSession->execute($this->authenticatedSession->get());

        return response()->json([
            'status' => true,
            'message' => 'Sesión activa.',
            'data' => (new AuthenticationSessionResource($session))->resolve($request),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $session = $this->refreshSession->execute($this->authenticatedSession->get(), $this->sessionTtlMinutes());

        return response()->json([
            'status' => true,
            'message' => 'Sesión renovada.',
            'data' => [...(new AuthenticationSessionResource($session))->resolve($request), 'session_ready' => true],
        ])->withCookie($this->sessionCookie($session->token));
    }

    public function switchEnterprise(SwitchAuthenticationEnterpriseRequest $request): JsonResponse
    {
        $session = $this->switchEnterprise->execute(
            $this->authenticatedSession->get(),
            $request->switchEnterprise(),
            $this->sessionTtlMinutes(),
        );

        return response()->json([
            'status' => true,
            'message' => 'Empresa activa cambiada.',
            'data' => (new AuthenticationSessionResource($session))->resolve($request),
        ])->withCookie($this->sessionCookie($session->token));
    }

    public function destroySession(Request $request): JsonResponse
    {
        $this->logout->execute($this->authenticatedSession->get()->tokenHash);

        return response()->json([
            'status' => true,
            'message' => 'Sesión cerrada.',
            'data' => null,
        ])->withCookie($this->forgetCookie((string) config('auth.v3_session_cookie_name', 'billing_v3_session')));
    }

    public function activeSessions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Sesiones activas cargadas.',
            'data' => $this->security->activeSessions($this->authenticatedSession->get()),
        ]);
    }

    public function revokeOtherSessions(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Otras sesiones revocadas.',
            'data' => $this->security->revokeOtherSessions($this->authenticatedSession->get()),
        ]);
    }

    public function revokeActiveSession(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Sesión revocada.',
            'data' => $this->security->revokeSession($this->authenticatedSession->get(), $id),
        ]);
    }

    public function verifyPassword(VerifyAuthenticationPasswordRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Contraseña verificada.',
            'data' => $this->security->verifyPassword($this->authenticatedSession->get(), (string) $request->validated('password')),
        ]);
    }

    public function changePassword(ChangeAuthenticationPasswordRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Contraseña actualizada.',
            'data' => $this->security->changePassword(
                $this->authenticatedSession->get(),
                (string) $request->validated('current_password'),
                (string) $request->validated('new_password'),
            ),
        ]);
    }

    public function mfaStatus(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Estado MFA cargado.',
            'data' => $this->security->mfaStatus($this->authenticatedSession->get()),
        ]);
    }

    public function beginMfa(BeginAuthenticationMfaRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Configuración MFA iniciada.',
            'data' => $this->security->beginMfa($this->authenticatedSession->get(), (string) $request->validated('current_password')),
        ]);
    }

    public function confirmMfa(ConfirmAuthenticationMfaRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'MFA confirmado.',
            'data' => $this->security->confirmMfa($this->authenticatedSession->get(), (string) $request->validated('code')),
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Preferencias cargadas.',
            'data' => ['preferences' => $this->security->preferences($this->authenticatedSession->get())],
        ]);
    }

    public function savePreferences(UpdateAuthenticationPreferencesRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Preferencias guardadas.',
            'data' => ['preferences' => $this->security->savePreferences($this->authenticatedSession->get(), (array) $request->validated('preferences'))],
        ]);
    }

    public function supportPasswordReset(Request $request, int $id): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'La solicitud quedó registrada; la entrega de correo debe ser gestionada por el proveedor configurado.',
            'data' => $this->security->requestSupportPasswordReset($this->authenticatedSession->get(), $id),
        ]);
    }

    /** @return array<string, mixed> */
    private function readChallenge(Request $request): array
    {
        $encrypted = $request->cookie((string) config('auth.v3_challenge_cookie_name', 'billing_v3_challenge'));
        if (! is_string($encrypted) || $encrypted === '') {
            throw new AuthenticationException('El desafío de autenticación expiró.', 'authentication_failed', 401);
        }

        $challenge = json_decode(Crypt::decryptString($encrypted), true);
        if (! is_array($challenge)) {
            throw new AuthenticationException('El desafío de autenticación no es válido.', 'authentication_failed', 401);
        }

        return $challenge;
    }

    /**
     * @param  array{user_id: int, enterprise_ids: int[], expires_at: int}  $payload
     *
     * @throws JsonException
     */
    private function challengeCookie(array $payload): Cookie
    {
        return cookie()->make(
            (string) config('auth.v3_challenge_cookie_name', 'billing_v3_challenge'),
            Crypt::encryptString(json_encode($payload, JSON_THROW_ON_ERROR)),
            5,
            (string) config('auth.v3_cookie_path', '/'),
            config('auth.v3_cookie_domain'),
            (bool) config('auth.v3_cookie_secure', false),
            true,
            false,
            (string) config('auth.v3_cookie_same_site', 'lax'),
        );
    }

    private function sessionCookie(string $token): Cookie
    {
        return cookie()->make(
            (string) config('auth.v3_session_cookie_name', 'billing_v3_session'),
            $token,
            (int) config('auth.v3_session_ttl', 120),
            (string) config('auth.v3_cookie_path', '/'),
            config('auth.v3_cookie_domain'),
            (bool) config('auth.v3_cookie_secure', false),
            true,
            false,
            (string) config('auth.v3_cookie_same_site', 'lax'),
        );
    }

    private function forgetCookie(string $name): Cookie
    {
        return cookie()->forget($name, (string) config('auth.v3_cookie_path', '/'), config('auth.v3_cookie_domain'));
    }

    private function sessionTtlMinutes(): int
    {
        return max(5, min(24 * 60, (int) config('auth.v3_session_ttl', 120)));
    }
}
