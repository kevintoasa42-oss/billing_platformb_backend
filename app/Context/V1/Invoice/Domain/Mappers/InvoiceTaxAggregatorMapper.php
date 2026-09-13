<?php

namespace App\Context\V1\Invoice\Domain\Mappers;

use App\Context\V1\Invoice\Domain\Models\InvoiceTax;

/**
 * Maps aggregated tax arrays (from calculation) into InvoiceTax domain objects.
 * Separates object construction from calculation logic.
 */
class InvoiceTaxAggregatorMapper
{
    /**
     * @param  array  $aggregated  Array of aggregated tax data keyed by sri_iva_percentage_id
     * @return InvoiceTax[]
     */
    public static function fromAggregated(array $aggregated): array
    {
        return array_map(
            fn ($t) => new InvoiceTax(
                sri_iva_percentage_id: $t['sri_iva_percentage_id'],
                code: $t['code'],
                percentage_code: $t['percentage_code'],
                rate: $t['rate'],
                tax_base: round($t['tax_base'], 2),
                tax: round($t['tax'], 2),
            ),
            array_values($aggregated)
        );
    }
}
