<?php

namespace App\Context\V1\Signature\Application\UseCases;

use App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface;

class ListSignaturesUseCase
{
    public function __construct(
        private SignatureRepositoryInterface $repository,
    ) {}

    public function execute(int $page = 1, int $perPage = 15, ?string $search = null): array
    {
        return $this->repository->listPaginated($page, $perPage, $search);
    }
}
