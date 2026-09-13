<?php

namespace App\Context\V1\EmissionPoints\Application\Adapters;

use App\Context\V1\EmissionPoints\Application\DTOs\NextSequentialDTO;
use App\Context\V1\EmissionPoints\Application\UseCases\GetNextSequentialUseCase;

final class EmissionPointSequentialService implements EmissionPointSequentialServiceInterface
{
    public function __construct(private GetNextSequentialUseCase $useCase) {}

    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null): NextSequentialDTO
    {
        return $this->useCase->execute($branchOfficeId, $emissionPointId, $emissionPoint);
    }
}
