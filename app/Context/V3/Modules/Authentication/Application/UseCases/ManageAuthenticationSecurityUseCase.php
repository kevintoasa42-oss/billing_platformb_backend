<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationSecurityRepositoryInterface;

final readonly class ManageAuthenticationSecurityUseCase
{
    public function __construct(private AuthenticationSecurityRepositoryInterface $repository) {}

    /** @return list<array<string, mixed>> */
    public function activeSessions(AuthenticationSession $current): array
    {
        return $this->repository->activeSessions($current);
    }

    /** @return array{revoked: bool} */
    public function revokeSession(AuthenticationSession $current, int $sessionLegacyId): array
    {
        return ['revoked' => $this->repository->revokeSession($current, $sessionLegacyId)];
    }

    /** @return array{revoked: int} */
    public function revokeOtherSessions(AuthenticationSession $current): array
    {
        return ['revoked' => $this->repository->revokeOtherSessions($current)];
    }

    /** @return array{verified: bool} */
    public function verifyPassword(AuthenticationSession $current, string $password): array
    {
        if (! $this->repository->verifyPassword($current->userId, $password)) {
            throw new AuthenticationException('La contraseña no es correcta.', 'invalid_password', 422);
        }

        return ['verified' => true];
    }

    /** @return array{changed: bool} */
    public function changePassword(AuthenticationSession $current, string $currentPassword, string $newPassword): array
    {
        $this->verifyPassword($current, $currentPassword);
        $this->repository->changePassword($current->userId, $newPassword);

        return ['changed' => true];
    }

    /** @return array{enabled: bool, confirmed_at: ?string} */
    public function mfaStatus(AuthenticationSession $current): array
    {
        return $this->repository->mfaStatus($current->userId);
    }

    /** @return array{url: string, barcode: string} */
    public function beginMfa(AuthenticationSession $current, string $password): array
    {
        $this->verifyPassword($current, $password);

        return $this->repository->beginMfa($current->userId);
    }

    /** @return array{enabled: bool, confirmed_at: ?string} */
    public function confirmMfa(AuthenticationSession $current, string $code): array
    {
        return $this->repository->confirmMfa($current->userId, $code);
    }

    /** @return array<string, mixed> */
    public function preferences(AuthenticationSession $current): array
    {
        return $this->repository->preferences($current->userId);
    }

    /** @param array<string, mixed> $preferences */
    public function savePreferences(AuthenticationSession $current, array $preferences): array
    {
        return $this->repository->savePreferences($current->userId, $preferences);
    }

    /** @return array{user_id: int, delivery: array{attempted: bool, success: bool, delivery_confirmed: bool, expires_at: string}} */
    public function requestSupportPasswordReset(AuthenticationSession $current, int $userLegacyId): array
    {
        if (! $current->platformAdmin) {
            throw new AuthenticationException('No tienes permiso para solicitar este restablecimiento.', 'forbidden', 403);
        }
        if (! $this->repository->hasActiveUserWithLegacyId($userLegacyId)) {
            throw new AuthenticationException('No se encontró una cuenta activa.', 'not_found', 404);
        }

        return [
            'user_id' => $userLegacyId,
            'delivery' => [
                'attempted' => true,
                'success' => false,
                'delivery_confirmed' => false,
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
            ],
        ];
    }
}
