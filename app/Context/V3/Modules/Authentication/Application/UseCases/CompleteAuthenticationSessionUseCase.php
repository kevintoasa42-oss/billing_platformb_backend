<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Application\DTOs\AuthenticationSessionDTO;
use App\Context\V3\Modules\Authentication\Application\DTOs\CompleteSessionDTO;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;

final readonly class CompleteAuthenticationSessionUseCase
{
    public function __construct(private AuthenticationRepositoryInterface $repository) {}

    public function execute(CompleteSessionDTO $session, int $ttlMinutes = 120): AuthenticationSessionDTO
    {
        if ($session->userId === '' || $session->expiresAt < now()->getTimestamp()) {
            throw new AuthenticationException('El desafío de autenticación expiró.', 'authentication_failed', 401);
        }
        if (! in_array($session->enterpriseId, $session->allowedEnterpriseIds, true)) {
            throw new AuthenticationException('La empresa seleccionada no está disponible para esta cuenta.', 'forbidden', 403);
        }

        $user = $this->repository->findUserById($session->userId);
        $enterprise = $this->repository->enterpriseForUser($session->userId, $session->enterpriseId);
        if (! $user || ! $enterprise) {
            throw new AuthenticationException('La empresa seleccionada no está disponible para esta cuenta.', 'forbidden', 403);
        }
        $token = bin2hex(random_bytes(32));
        $record = $this->repository->createSession(
            $enterprise,
            $user->id,
            hash('sha256', $token),
            $ttlMinutes,
        );

        return new AuthenticationSessionDTO(
            token: $token,
            user: $user,
            enterprise: $enterprise,
            expiresAt: $record->expiresAt,
        );
    }
}
