<?php

namespace App\Context\V1\EmissionPoints\Application\Adapters;

use App\Context\V1\EmissionPoints\Application\DTOs\NextSequentialDTO;

/** Application-facing contract for other bounded contexts. */
interface EmissionPointSequentialServiceInterface
{
    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null): NextSequentialDTO;
}
