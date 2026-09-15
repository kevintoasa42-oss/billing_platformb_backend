<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Product\Domain\Models\ProductTax;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductTaxAssignmentModel;

class ProductTaxMapper
{
    public function toDomain(ProductTaxAssignmentModel $record, int $productLegacyId): ProductTax
    {
        return new ProductTax(
            id: (int) $record->legacy_id,
            productId: $productLegacyId,
            sriIvaTypeId: (int) $record->sri_iva_type_id,
            taxName: $record->tax_name,
            percentage: (string) $record->percentage,
            sriCode: $record->sri_code,
            isActive: (bool) $record->is_active,
        );
    }
}
