<?php

namespace App\Context\V1\EmissionPoints\Application\Adapters;

use App\Context\V1\EmissionPoints\Application\DTOs\NextSequentialDTO;
use App\Context\V1\EmissionPoints\Application\UseCases\GetNextSequentialUseCase;
use App\Context\V1\EmissionPoints\Application\UseCases\TakeNextSequentialUseCase;

final class EmissionPointSequentialService implements EmissionPointSequentialServiceInterface
{
    public function __construct(
        private GetNextSequentialUseCase  $getNextSequentialUseCase,
        private TakeNextSequentialUseCase $takeNextSequentialUseCase,
    )
    {
    }

    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null): NextSequentialDTO
    {
        return $this->getNextSequentialUseCase->execute($branchOfficeId, $emissionPointId, $emissionPoint);
    }

    public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null): NextSequentialDTO
    {
        return $this->takeNextSequentialUseCase->execute($branchOfficeId, $emissionPointId, $emissionPoint);
    }
}
