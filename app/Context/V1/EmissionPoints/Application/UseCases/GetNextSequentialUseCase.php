<?php

namespace App\Context\V1\EmissionPoints\Application\UseCases;

use App\Context\V1\EmissionPoints\Application\DTOs\NextSequentialDTO;
use App\Context\V1\EmissionPoints\Domain\Ports\NextSequentialGeneratorInterface;

final class GetNextSequentialUseCase
{
    public function __construct(private NextSequentialGeneratorInterface $generator) {}

    public function execute(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null): NextSequentialDTO
    {
        $nextSequential = $this->generator->nextSequential($branchOfficeId, $emissionPointId, $emissionPoint);

        return new NextSequentialDTO(
            branch_office_id: $branchOfficeId,
            emission_point_id: $emissionPointId,
            emission_point: $emissionPoint,
            sequential: $nextSequential->sequential,
            formatted_sequential: $nextSequential->formatted(),
        );
    }
}
