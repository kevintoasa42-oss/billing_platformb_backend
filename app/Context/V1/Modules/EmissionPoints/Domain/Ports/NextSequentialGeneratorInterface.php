<?php

namespace App\Context\V1\Modules\EmissionPoints\Domain\Ports;

use App\Context\V1\Modules\EmissionPoints\Domain\Models\EmissionPointSequential;

interface NextSequentialGeneratorInterface
{
    /**
     * Reads the next sequential without changing it. Exactly one point
     * selector must be supplied.
     */
    public function nextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential;

    /**
     * Atomically takes the next sequential and advances the stored counter.
     */
    public function takeNextSequential(int $branchOfficeId, ?int $emissionPointId = null, ?string $emissionPoint = null, ?int $carrierId = null, string $documentCode = '01', string $documentLabel = 'Factura'): EmissionPointSequential;
}
