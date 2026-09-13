<?php

namespace App\Context\V1\EmissionPoints\Application\Adapters;

use App\Context\V1\EmissionPoints\Application\DTOs\NextSequentialDTO;

/** Application-facing contract for other bounded contexts. */
interface EmissionPointSequentialServiceInterface
{
    /** Returns the next sequential without modifying the counter. */
    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $partnerId = null): NextSequentialDTO;

    /** Takes a sequential and atomically advances the counter. */
    public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $partnerId = null): NextSequentialDTO;
}
