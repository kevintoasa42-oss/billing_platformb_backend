<?php

namespace App\Context\V1\Invoice\Domain\Repositories;

/**
 * Contract for reading SRI catalogs (central DB).
 * Implemented in Infrastructure with Eloquent.
 */
interface SriCatalogRepositoryInterface
{
    /**
     * Get all IVA percentages keyed by ID.
     *
     * @return array<int, object{code: string, percentage: ?float}>
     */
    public function getIvaPercentages(): array;

    /**
     * Get all payment methods keyed by ID.
     *
     * @return array<int, object{code: string}>
     */
    public function getPaymentMethods(): array;
}
