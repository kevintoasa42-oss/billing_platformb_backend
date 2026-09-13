<?php

namespace App\Context\V1\Modules\Signature\Application\UseCases;

use App\Context\V1\Modules\Signature\Application\DTOs\SignatureDTO;
use App\Context\V1\Modules\Signature\Domain\Mappers\SignatureMapper;
use App\Context\V1\Modules\Signature\Domain\Repositories\SignatureRepositoryInterface;

class CreateSignatureUseCase
{
    public function __construct(
        private SignatureRepositoryInterface $repository,
    ) {}

    public function execute(SignatureDTO $dto): SignatureDTO
    {
        $signature = SignatureMapper::fromDto($dto);

        return SignatureDTO::fromArray($this->repository->create($signature));
    }
}
