<?php

namespace App\Context\V1\Modules\Invoice\Domain\Repositories;

/**
 * Contract for resolving signature configuration (environment, emission_type).
 * Implemented in Infrastructure with Eloquent.
 */
interface SignatureConfigRepositoryInterface
{
    /**
     * Get the active signature configuration.
     *
     * If carrierId is null, resolves from enterprise_signatures (central DB)
     * using the given enterpriseId.
     * If carrierId is set, resolves from signatures (tenant DB).
     *
     * @param  int|null  $carrierId  null = enterprise signature
     * @param  int  $enterpriseId  used when carrierId is null
     * @return object{environment: string, emission_type: bool}|null
     */
    public function getActiveConfig(?int $carrierId, int $enterpriseId): ?object;
}
