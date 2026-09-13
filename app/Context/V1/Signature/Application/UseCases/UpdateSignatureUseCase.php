<?php

namespace App\Context\V1\Signature\Application\UseCases;

use App\Context\V1\Signature\Application\DTOs\SignatureDTO;
use App\Context\V1\Signature\Domain\Mappers\SignatureMapper;
use App\Context\V1\Signature\Domain\Repositories\SignatureRepositoryInterface;

class UpdateSignatureUseCase
{
    public function __construct(
        private SignatureRepositoryInterface $repository,
    ) {}

    public function execute(SignatureDTO $dto): SignatureDTO
    {
        $signature = SignatureMapper::fromDto($dto);

        return SignatureDTO::fromArray($this->repository->update($signature));
    }
}
