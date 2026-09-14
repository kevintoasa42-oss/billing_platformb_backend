<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Application\DTOs\AuthenticationSessionDTO;
use App\Context\V3\Modules\Authentication\Application\DTOs\SwitchEnterpriseDTO;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;

final readonly class SwitchAuthenticationEnterpriseUseCase
{
    public function __construct(private AuthenticationRepositoryInterface $repository) {}

    public function execute(
        AuthenticationSession $currentSession,
        SwitchEnterpriseDTO $switch,
        int $ttlMinutes = 120,
    ): AuthenticationSessionDTO {
        $user = $this->repository->findUserById($currentSession->userId);
        $enterprise = $this->repository->enterpriseForUser($currentSession->userId, $switch->enterpriseId);
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
        $this->repository->revokeSession($currentSession->tokenHash);

        return new AuthenticationSessionDTO($token, $user, $enterprise, $record->expiresAt);
    }
}
