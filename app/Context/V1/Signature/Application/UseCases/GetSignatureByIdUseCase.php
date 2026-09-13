<?php

namespace App\Context\V1\Signature\Application\UseCases;

use App\Context\V1\Signature\Application\DTOs\SignatureDTO;
use App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface;

class GetSignatureByIdUseCase
{
    public function __construct(
        private SignatureRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?SignatureDTO
    {
        $data = $this->repository->getById($id);

        if (!$data) {
            return null;
        }

        return SignatureDTO::fromArray($data);
    }
}
