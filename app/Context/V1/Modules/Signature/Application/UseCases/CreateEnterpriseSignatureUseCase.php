<?php

namespace App\Context\V1\Modules\Signature\Application\UseCases;

use App\Context\V1\Modules\Signature\Application\DTOs\EnterpriseSignatureDTO;
use App\Context\V1\Modules\Signature\Domain\Mappers\EnterpriseSignatureMapper;
use App\Context\V1\Modules\Signature\Domain\Repositories\EnterpriseSignatureRepositoryInterface;

class CreateEnterpriseSignatureUseCase
{
    public function __construct(
        private EnterpriseSignatureRepositoryInterface $repository,
    ) {}

    public function execute(EnterpriseSignatureDTO $dto): EnterpriseSignatureDTO
    {
        $signature = EnterpriseSignatureMapper::fromDto($dto);

        return EnterpriseSignatureDTO::fromArray($this->repository->create($signature));
    }
}
