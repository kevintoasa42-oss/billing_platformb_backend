<?php

namespace App\Context\V1\Enterprise\Application\UseCases;

use App\Context\V1\Enterprise\Domain\Repositories\UserRepositoryInterface;

class AssignRoleToUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Asigna un rol a un user.
     *
     * @param  int  $userId
     * @param  int  $rolId
     * @return void
     */
    public function ejecutar(int $userId, int $rolId): void
    {
        $this->userRepository->asignarRol($userId, $rolId);
    }
}
