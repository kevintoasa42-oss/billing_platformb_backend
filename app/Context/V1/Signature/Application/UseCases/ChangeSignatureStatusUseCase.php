<?php

namespace App\Context\V1\Signature\Application\UseCases;

use App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface;

class ChangeSignatureStatusUseCase
{
    public function __construct(
        private SignatureRepositoryInterface $repository,
    ) {}

    public function execute(int $id, bool $status): bool
    {
        return $this->repository->changeStatus($id, $status);
    }
}
