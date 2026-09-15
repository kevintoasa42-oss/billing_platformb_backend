<?php

namespace App\Context\V1\Modules\EmissionPoints\Application\Adapters;

use App\Context\V1\Modules\EmissionPoints\Application\DTOs\NextSequentialDTO;
use App\Context\V1\Modules\EmissionPoints\Application\UseCases\GetNextSequentialUseCase;
use App\Context\V1\Modules\EmissionPoints\Application\UseCases\TakeNextSequentialUseCase;

final readonly class EmissionPointSequentialService implements EmissionPointSequentialServiceInterface
{
    public function __construct(
        private GetNextSequentialUseCase $getNextSequentialUseCase,
        private TakeNextSequentialUseCase $takeNextSequentialUseCase,
    ) {}

    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01'): NextSequentialDTO
    {
        return $this->getNextSequentialUseCase->execute($branchOfficeId, $emissionPointId, $emissionPoint, $carrierId, $documentCode);
    }

    public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01'): NextSequentialDTO
    {
        return $this->takeNextSequentialUseCase->execute($branchOfficeId, $emissionPointId, $emissionPoint, $carrierId, $documentCode);
    }
}
