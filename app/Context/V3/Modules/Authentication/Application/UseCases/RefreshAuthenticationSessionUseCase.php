<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Application\DTOs\AuthenticationSessionDTO;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;

final readonly class RefreshAuthenticationSessionUseCase
{
    public function __construct(private AuthenticationRepositoryInterface $repository) {}

    public function execute(AuthenticationSession $currentSession, int $ttlMinutes = 120): AuthenticationSessionDTO
    {
        $user = $this->repository->findUserById($currentSession->userId);
        $enterprise = $this->repository->enterpriseForUser($currentSession->userId, $currentSession->tenantId);

        if (! $user || ! $enterprise) {
            throw new AuthenticationException('La sesión no es válida.', 'unauthenticated', 401);
        }

        $token = bin2hex(random_bytes(32));
        $record = $this->repository->createSession(
            $enterprise,
            $user->id,
            hash('sha256', $token),
            $ttlMinutes,
        );
        $this->repository->revokeSession($currentSession->tokenHash);

        return new AuthenticationSessionDTO(
            token: $token,
            user: $user,
            enterprise: $enterprise,
            expiresAt: $record->expiresAt,
            enterprises: $this->repository->enterprisesForUser($user->id),
        );
    }
}
