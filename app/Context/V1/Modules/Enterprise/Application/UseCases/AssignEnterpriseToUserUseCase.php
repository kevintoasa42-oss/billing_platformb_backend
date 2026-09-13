<?php

namespace App\Context\V1\Modules\Enterprise\Application\UseCases;

use App\Context\V1\Modules\Enterprise\Domain\Repositories\UserRepositoryInterface;

class AssignEnterpriseToUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Asigna una enterprise a un user (un user puede estar en varias enterprises).
     *
     * @param  int  $userId
     * @param  int  $enterpriseId
     * @return void
     */
    public function ejecutar(int $userId, int $enterpriseId): void
    {
        $this->userRepository->asignarEnterprise($userId, $enterpriseId);
    }
}
