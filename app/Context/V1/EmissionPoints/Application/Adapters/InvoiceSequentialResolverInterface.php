<?php

namespace App\Context\V1\EmissionPoints\Application\Adapters;

/**
 * Application-facing contract for the Invoice bounded context.
 *
 * Resolves SRI establishment/emission-point codes from their database IDs
 * and produces a unique sequential number, preventing duplicates even under
 * concurrent access.
 */
interface InvoiceSequentialResolverInterface
{
    /**
     * Resolve SRI codes and generate a unique sequential.
     *
     * If $requestedSequential is provided and not already used, it is kept
     * and the internal counter is advanced past it.  If it is already used
     * (or null), a fresh sequential is taken from the atomic counter.
     *
     * @param int      $branchOfficeId    Branch office primary key
     * @param int      $emissionPointId   Emission point primary key
     * @param string|null $requestedSequential  Optional sequential sent by the frontend
     * @param int|null $excludeInvoiceId  Exclude this invoice ID when checking duplicates (for updates)
     * @return array{establishment: string, emission_point: string, sequential: string}
     *
     * @throws \App\Context\V1\EmissionPoints\Domain\Exceptions\EmissionPointNotFoundException
     */
    public function resolve(
        int $branchOfficeId,
        int $emissionPointId,
        ?string $requestedSequential = null,
        ?int $excludeInvoiceId = null,
    ): array;
}
