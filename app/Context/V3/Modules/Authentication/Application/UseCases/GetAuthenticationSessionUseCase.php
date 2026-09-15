<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Application\DTOs\AuthenticationSessionDTO;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Models\AuthenticationSession;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;

final readonly class GetAuthenticationSessionUseCase
{
    public function __construct(private AuthenticationRepositoryInterface $repository) {}

    public function execute(AuthenticationSession $session): AuthenticationSessionDTO
    {
        $user = $this->repository->findUserById($session->userId);
        $enterprise = $this->repository->enterpriseForUser($session->userId, $session->tenantId);
        if (! $user || ! $enterprise) {
            throw new AuthenticationException('La sesión no es válida.', 'unauthenticated', 401);
        }

        $permissions = $session->capabilities === [] ? $enterprise->capabilities : $session->capabilities;

        return new AuthenticationSessionDTO(
            token: null,
            user: $user,
            enterprise: $enterprise,
            expiresAt: $session->expiresAt,
            enterprises: $this->repository->enterprisesForUser($user->id),
            menus: $this->repository->menuTreeForTenant($session->tenantId),
            permissions: $permissions,
        );
    }
}
