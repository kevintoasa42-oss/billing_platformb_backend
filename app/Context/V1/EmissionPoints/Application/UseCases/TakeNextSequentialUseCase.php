<?php

namespace App\Context\V1\EmissionPoints\Application\UseCases;

use App\Context\V1\EmissionPoints\Application\DTOs\NextSequentialDTO;
use App\Context\V1\EmissionPoints\Domain\Ports\NextSequentialGeneratorInterface;

/**
 * Takes a sequential for a document and advances the counter atomically.
 */
final class TakeNextSequentialUseCase
{
    public function __construct(private NextSequentialGeneratorInterface $generator)
    {
    }

    public function execute(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $partnerId = null): NextSequentialDTO
    {
        $takenSequential = $this->generator->takeNextSequential($branchOfficeId, $emissionPointId, $emissionPoint, $partnerId);

        return new NextSequentialDTO(
            branch_office_id: $branchOfficeId,
            emission_point_id: $emissionPointId,
            emission_point: $emissionPoint,
            partner_id: $partnerId,
            sequential: $takenSequential->sequential,
            formatted_sequential: $takenSequential->formatted(),
        );
    }
}
