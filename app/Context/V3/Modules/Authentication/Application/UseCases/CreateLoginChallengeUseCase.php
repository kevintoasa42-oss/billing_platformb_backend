<?php

namespace App\Context\V3\Modules\Authentication\Application\UseCases;

use App\Context\V3\Modules\Authentication\Application\DTOs\CredentialsDTO;
use App\Context\V3\Modules\Authentication\Application\DTOs\LoginChallengeDTO;
use App\Context\V3\Modules\Authentication\Domain\Exceptions\AuthenticationException;
use App\Context\V3\Modules\Authentication\Domain\Repositories\AuthenticationRepositoryInterface;

final readonly class CreateLoginChallengeUseCase
{
    public function __construct(private AuthenticationRepositoryInterface $repository) {}

    public function execute(CredentialsDTO $credentials): LoginChallengeDTO
    {
        $user = $this->repository->findUserByEmail($credentials->email);
        if (! $user || ! $user->active || ! password_verify($credentials->password, $user->passwordHash)) {
            throw new AuthenticationException('El correo o la contraseña no son correctos.');
        }

        $enterprises = $this->repository->enterprisesForUser($user->id);
        if ($enterprises === []) {
            throw new AuthenticationException('La cuenta no tiene empresas asignadas.', 'forbidden', 403);
        }

        return new LoginChallengeDTO($user, $enterprises, now()->addMinutes(5)->getTimestamp());
    }
}
