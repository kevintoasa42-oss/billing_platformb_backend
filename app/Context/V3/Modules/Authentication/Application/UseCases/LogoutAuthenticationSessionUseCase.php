<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;

final readonly class LogoutAuthenticationSessionUseCase
{
    public function __construct(private AuthenticationRepositoryInterface $repository) {}

    public function execute(string $tokenHash): void
    {
        $this->repository->revokeSession($tokenHash);
    }
}
