<?php

namespace App\Context\V3\Modules\Core\Product\Infrastructure\Mappers;

use App\Context\V3\Modules\Core\Product\Domain\Models\Product;
use App\Context\V3\Modules\Core\Product\Infrastructure\Laravel\Eloquent\Models\ProductModel;

class ProductMapper
{
    public function toDomain(ProductModel $record): Product
    {
        $taxes = [];
        if ($record->relationLoaded('taxAssignments')) {
            $taxes = $record->taxAssignments->where('is_active', true)->map(fn ($t): array => [
                'id' => (int) $t->legacy_id,
                'product_id' => (int) $record->legacy_id,
                'sri_iva_type_id' => (int) $t->sri_iva_type_id,
                'tax_name' => $t->tax_name,
                'percentage' => (string) $t->percentage,
                'sri_code' => $t->sri_code,
                'is_active' => (bool) $t->is_active,
            ])->values()->all();
        }

        return new Product(
            uuid: (string) $record->id,
            id: (int) $record->legacy_id,
            name: $record->name,
            referencePrice: (string) $record->unit_price,
            unitPrice: (string) $record->unit_price,
            sriPrincipalCode: $record->sri_principal_code,
            isActive: (bool) $record->is_active,
            type: $record->type,
            barcode: $record->barcode,
            auxiliaryCode: $record->auxiliary_code,
            otherCode: $record->other_code,
            description: $record->description,
            activityId: $record->activity_id,
            taxes: $taxes,
        );
    }

    /**
     * @param  iterable<int, ProductModel>  $records
     * @return array<int, Product>
     */
    public function toDomainList(iterable $records): array
    {
        $list = [];
        foreach ($records as $record) {
            $list[] = $this->toDomain($record);
        }

        return $list;
    }

    /**
     * @param  Product  $product
     * @return array<string, mixed>
     */
    public function toDatabaseArray(Product $product): array
    {
        $data = [
            'name' => $product->name,
            'unit_price' => $product->unitPrice ?? $product->referencePrice,
            'sri_principal_code' => $product->sriPrincipalCode,
            'is_active' => $product->isActive ?? true,
            'type' => $product->type,
            'barcode' => $product->barcode,
            'auxiliary_code' => $product->auxiliaryCode,
            'other_code' => $product->otherCode,
            'description' => $product->description,
            'activity_id' => $product->activityId,
        ];

        return array_filter($data, fn ($value): bool => $value !== null);
    }
}
