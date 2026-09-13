<?php

namespace App\Context\V1\Signature\Application\UseCases;

use App\Context\V1\Signature\Application\DTOs\EnterpriseSignatureDTO;
use App\Context\V1\Signature\Domain\Repositories\EnterpriseSignatureRepositoryInterface;

class GetEnterpriseSignatureUseCase
{
    public function __construct(
        private EnterpriseSignatureRepositoryInterface $repository,
    ) {}

    public function execute(int $enterpriseId): ?EnterpriseSignatureDTO
    {
        $data = $this->repository->getByEnterpriseId($enterpriseId);

        if (!$data) {
            return null;
        }

        return EnterpriseSignatureDTO::fromArray($data);
    }
}
