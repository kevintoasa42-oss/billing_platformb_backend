<?php

namespace App\Context\V1\EmissionPoints\Domain\Ports;

interface NextSequentialGeneratorInterface
{
    /**
     * Reserves and returns the next sequential for a branch office and one of
     * its emission points. Exactly one point selector must be supplied.
     */
    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null): int;
}
