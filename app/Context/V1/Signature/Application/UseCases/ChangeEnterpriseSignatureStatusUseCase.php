<?php

namespace App\Context\V1\Signature\Application\UseCases;

use App\Context\V1\Signature\Domain\Repositories\EnterpriseSignatureRepositoryInterface;

class ChangeEnterpriseSignatureStatusUseCase
{
    public function __construct(
        private EnterpriseSignatureRepositoryInterface $repository,
    ) {}

    public function execute(int $id, bool $status): bool
    {
        return $this->repository->changeStatus($id, $status);
    }
}
