<?php

namespace App\Context\V1\Modules\EmissionPoints\Domain\Exceptions;

use RuntimeException;

final class EmissionPointNotFoundException extends RuntimeException
{
    public function __construct(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null)
    {
        $selector = $emissionPointId !== null ? "id {$emissionPointId}" : "code {$emissionPoint}";
        parent::__construct("Emission point {$selector} was not found for branch office {$branchOfficeId}.");
    }
}
